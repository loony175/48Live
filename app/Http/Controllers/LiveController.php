<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Team;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class LiveController extends Controller
{
	const HEADER = [
		'Content-type' => 'application/json',
		'version' => '5.0.1',
		'os' => 'Android'
	];
	const METHOD_POST = 'POST';

	const URL_MEMBER_LIVE_PAGE = 'https://plive.48.cn/livesystem/api/live/v1/memberLivePage';
	const URL_MEMBER_LIVE_SHOW = 'https://plive.48.cn/livesystem/api/live/v1/getLiveOne';

	const URL_CHATROOM_TOKEN = 'https://live.48.cn/Server/do_ajax_setcookie';

	/**
	 * 获取直播列表
	 * @return array
	 */
	public function list()
	{
		$body = [
			'lastTime' => '0',
			'groupId' => '0',
			'type' => '0',
			'memberId' => $this->request->memberId ?? '0',
			'giftUpdTime' => '1498211389003',
			'limit' => $this->request->limit ?? 800
		];

		$result = $this->send(self::URL_MEMBER_LIVE_PAGE, $body);
		if(!$result){
			return $this->failed('请求失败');
		}

		$members = Member::query()->select(['member_id', 'real_name', 'team_id'])->get()->toArray();
		$teams = Team::query()->select(['team_id', 'team_name', 'color', 'group_id'])->get()->toArray();

		$liveList = $result['content']['liveList'];
		foreach($liveList as $key => $live){
			$member = array_first($members, function($value, $index) use ($live){
				return $live['memberId'] == $value['member_id'];
			});
			$live['member_name'] = $member['real_name'];

			$live['team'] = array_first($teams, function($value, $index) use ($member){
				return $member['team_id'] == $value['team_id'];
			});

			$liveList[$key] = $live;
		}

		$reviewList = $result['content']['reviewList'];
		foreach($reviewList as $key => $review){
			$member = array_first($members, function($value, $index) use ($review){
				return $review['memberId'] == $value['member_id'];
			});
			$review['member_name'] = $member['real_name'];

			$review['team'] = array_first($teams, function($value, $index) use ($member){
				return $member['team_id'] == $value['team_id'];
			});
			$reviewList[$key] = $review;
		}

		$data = [
			'liveList' => $liveList,
			'reviewList' => $reviewList,
		];

		return $this->success($data);
	}

	/**
	 * 获取单个直播
	 * @param $liveId
	 * @return array
	 */
	public function show($liveId)
	{
		$body = [
			'type' => '1',
			'userId' => '0',
			'liveId' => $liveId
		];

		$result = $this->send(self::URL_MEMBER_LIVE_SHOW, $body);

		if(!$result){
			return $this->failed('请求失败');
		}

		$data = $result['content'];

		$data['member'] = Member::query()->where('member_id', $data['memberId'])->select([
			'member_id',
			'real_name',
			'team_id'
		])->first()->toArray();
		return $this->success($data);
	}

	/**
	 * 获取弹幕
	 * @return array
	 */
	public function barrage()
	{
		$url = $this->request->barrageUrl;
		if(!$url){
			return $this->failed('url不能为空');
		}

		try{
			$content = file_get_contents($url);
			$array = explode("\r\n", $content);
			$barrages = [];
			foreach($array as $item){
				$arr = explode(']', $item);
				if(!is_array($arr) || empty($arr) || count($arr) < 2){
					continue;
				}
				$time = str_replace('[', '', $arr[0]);
				$arr = explode("\t", $arr[1]);
				if(!is_array($arr) || empty($arr) || count($arr) < 2){
					continue;
				}
				$barrages[] = [
					'time' => $time,
					'username' => $arr[0],
					'content' => $arr[1]
				];
			}

			return $this->success([
				'barrages' => $barrages
			]);
		}catch(\Exception $exception){
			return $this->failed('弹幕获取失败，大概还在生成');
		}
	}

	/**
	 * 获取聊天室token
	 * @return array
	 */
	public function token()
	{
		$body = [
			'timestamp' => time(),
			'cookie_val' => $this->randomString(8),
			'type' => 2
		];

		$client = new Client();

		try{
			//我也不知道为毛要请求两次才行
			$client->request(self::METHOD_POST, self::URL_CHATROOM_TOKEN, [
				'form_params' => $body
			]);
			$response = $client->request(self::METHOD_POST, self::URL_CHATROOM_TOKEN, [
				'form_params' => $body
			]);
			$body = json_decode($response->getBody(), true);
			return $this->success([
				'account' => $body['account'],
				'token' => $body['token']
			]);
		}catch(GuzzleException $e){
			return $this->failed('token获取失败');
		}
	}


	/**
	 * 生成随机cookie_val，用于未登录获取token
	 * @param int $length
	 * @return string
	 */
	private function randomString($length = 32)
	{
		$password = str_random($length);
		return '48web' . $password;
	}


	/**
	 * @param $url
	 * @param $body
	 * @return bool|Response
	 */
	private function send($url, $body)
	{
		$request = new Request(self::METHOD_POST, $url, self::HEADER, json_encode($body));
		$client = new Client();
		try{
			$response = $client->send($request);
			$body = json_decode($response->getBody(), true);
			if($body['status'] == 200){
				return $body;
			}else{
				\Log::debug($body['message']);
				return false;
			}
		}catch(GuzzleException $e){
			\Log::debug($e->getMessage());
			return false;
		}
	}
}

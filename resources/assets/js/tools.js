Date.prototype.format = function(fmt){
    const o = {
        "M+":this.getMonth() + 1,                 //月份
        "d+":this.getDate(),                    //日
        "h+":this.getHours(),                   //小时
        "m+":this.getMinutes(),                 //分
        "s+":this.getSeconds(),                 //秒
        "q+":Math.floor((this.getMonth() + 3) / 3), //季度
        "S":this.getMilliseconds()             //毫秒
    };
    if(/(y+)/.test(fmt)){
        fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    }
    for(const k in o){
        if(new RegExp("(" + k + ")").test(fmt)){
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length === 1) ? (o[k]) :
                (("00" + o[k]).substr(("" + o[k]).length)));
        }
    }
    return fmt;
};

import './48sdk/base-v2.8.0';
import './48sdk/nim-v2.8.0';
import axios from 'axios';
import Chatroom from './48sdk/chatroom-v2.8.0';

class Tools {
    /**
     *
     * @param picturesStr
     * @returns {any[]}
     */
    static pictureUrls(picturesStr){
        const pictures = picturesStr.split(',');
        return pictures.map(picture =>{
            if(picture.includes('http')){
                return picture;
            }else{
                return 'https://source.48.cn' + picture;
            }
        });
    }

    /**
     *
     * @param options
     * @returns {Promise<any>}
     */
    static chatroom(options){
        return new Promise((resolve, reject) =>{
            axios.get('/api/token').then(response =>{
                const chatroom = new Chatroom({
                    appKey:'632feff1f4c838541ab75195d1ceb3fa',      //从官网公演直播网页代码获取
                    account:response.data.data.account,
                    token:response.data.data.token,
                    chatroomId:options.roomId,
                    chatroomAddresses:[
                        'weblink04.netease.im:443',
                        /*'',*/
                    ],
                    onconnect:options.onConnect,
                    onerror:options.onError,
                    onwillreconnect:options.onWillConnnect,
                    ondisconnect:options.onDisconnect,
                    // // 消息
                    onmsgs:options.onMessage
                });
                resolve(chatroom);
            }).catch(error =>{
                reject(error);
            });
        });
    }

    static getMembers(){
        return new Promise((resolve, reject) =>{
            axios.get('/api/members').then(res =>{
                const members = res.data.data;
                resolve(members);
            }).catch(error =>{
                reject(error);
            });
        });

    }

    static getTeams(){
        return new Promise((resolve, reject) =>{
            axios.get('/api/teams').then(res =>{
                const members = res.data.data;
                resolve(members);
            }).catch(error =>{
                reject(error);
            });
        });
    }

    static timeToSecond(time){
        if(!time) return;
        const hours = time.split(':')[0];
        const minutes = time.split(':')[1];
        const seconds = time.split(':')[2];
        return Number(hours) * 3600 + Number(minutes) * 60 + Number(seconds);
    }

    static setSenderName(senderName){
        localStorage.setItem('senderName', senderName);
    }

    static getSenderName(){
        return localStorage.getItem('senderName');
    }

    static setVolume(volume){
        localStorage.setItem('volume', volume);
    }

    static getVolume(){
        return localStorage.getItem('volume') || 0.8;
    }
}

export default Tools;
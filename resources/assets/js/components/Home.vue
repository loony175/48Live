<template>
    <div class="layout">
        <Spin size="large" fix v-if="spinShow"></Spin>

        <Layout>
            <Header class="header">
                <div>
                    <Cascader filterable="" class="cascader" placeholder="请选择成员" :data="members"
                            v-model="selectedMember"></Cascader>

                    <span style="margin-left:16px;color:white;">获取条数</span>

                    <Tooltip content="最大值：4800" placement="bottom">
                        <InputNumber style="margin-left:8px;" :max="4800" :min="1" :step="160" v-model="limit"></InputNumber>
                    </Tooltip>

                    <Button type="primary" @click="getList">搜索</Button>
                </div>

                <div>
                    <gh-btns-star slug="Jarvay/48Live"></gh-btns-star>
                </div>
            </Header>
            <Content style="padding: 8px 32px;">
                <Card>
                    <div style="min-height: 200px;">
                        <Tabs type="card">
                            <TabPane label="直播">
                                <Card v-if="currentLiveList.length === 0" style="margin-bottom:8px">
                                    <p slot="title">当前没有直播</p>
                                </Card>

                                <Row v-for="index in Math.ceil(currentLiveList.length / 8)"
                                        :key="index">
                                    <Col style="padding: 4px;" span="3" v-for="(item, i) in currentLiveList"
                                            v-if="i <  index * 8 && i >= (index - 1) * 8" :key="item.liveId">
                                        <!--没找到48聊天室断开websocket的api，所以出现这种情况-->
                                        <a :href="getUrl(item)" target="_blank">
                                            <Card>
                                                <p slot="title">{{item.subTitle}}</p>

                                                <img ref="cover" class="cover" :src="item.cover">
                                                <p style="color:#ccc;">{{item.date}}</p>
                                                <div style="display: flex;justify-content: space-between;">
                                                    <div>
                                                        <span style="color: #000;">{{item.member_name}}</span>
                                                        <span class="team-badge"
                                                                :style="{'background-color':'#' + item.team.color}">{{item
                                                            .team.team_name}}</span>
                                                    </div>
                                                    <span v-if="item.liveType == 1">直播</span>
                                                    <span v-else>电台</span>
                                                </div>
                                            </Card>
                                        </a>
                                    </Col>
                                </Row>
                                <Page :current="livePage" :total="liveTotal" :page-size="pageSize" size="small"
                                        show-total
                                        @on-change="onLivePageChange"></Page>
                            </TabPane>

                            <TabPane label="回放">
                                <Row v-for="index in Math.ceil(currentReviewList.length / 8)"
                                        :key="index">
                                    <Col style="padding: 4px;" span="3" v-for="(item, i) in currentReviewList"
                                            v-if="i <  index * 8 && i >= (index - 1) * 8" :key="item.liveId">
                                        <a :href="getUrl(item)" target="_blank">
                                            <Card>
                                                <p slot="title">{{item.subTitle}}</p>

                                                <img ref="cover" class="cover" :src="item.cover">
                                                <p style="color:#ccc;">{{item.date}}</p>
                                                <div style="display: flex;justify-content: space-between;">
                                                    <div>
                                                        <span style="color: #000;">{{item.member_name}}</span>
                                                        <span class="team-badge"
                                                                :style="{'background-color':'#' + item.team.color}">
                                                            {{item.team.team_name}}</span>
                                                    </div>
                                                    <span v-if="item.liveType == 1">直播</span>
                                                    <span v-else>电台</span>
                                                </div>
                                            </Card>
                                        </a>
                                    </Col>
                                </Row>
                                <Page :current="reviewPage" :total="reviewTotal" :page-size="pageSize" size="small"
                                        show-total
                                        @on-change="onReviewPageChange"></Page>
                            </TabPane>
                        </Tabs>
                    </div>
                </Card>
            </Content>
            <Footer class="layout-footer-center">2018 &copy; Jarvay  超绝可爱黄婷婷</Footer>
        </Layout>
    </div>
</template>

<script>
    import Tools from "../tools";

    export default {
        name:'Home',
        data(){
            return {
                spinShow:true,
                liveList:[],
                reviewList:[],
                currentLiveList:[],
                currentReviewList:[],
                coverWidth:-1,
                pageSize:16,
                members:[],
                selectedMember:[],
                limit:80,
                liveTotal:0,
                reviewTotal:0,
                livePage:1,
                reviewPage:1,
            }
        },
        created:function(){
            this.getList();

            Tools.getInfo().then(info =>{
                this.members = info.groups.map(group =>{
                    return {
                        value:group.group_id,
                        label:group.group_name,
                        children:group.teams.map(team =>{
                            return {
                                value:team.team_id,
                                label:team.team_name,
                                children:team.members.map(member =>{
                                    return {
                                        value:member.member_id,
                                        label:member.real_name
                                    }
                                })
                            }
                        })
                    }
                });
            });
        },
        updated:function(){
            if(this.$refs.cover){
                if(this.coverWidth == -1){
                    this.coverWidth = this.$refs.cover[0].offsetWidth;
                }

                this.$refs.cover.forEach(item =>{
                    item.style.height = this.coverWidth + 'px';
                });
            }
        },
        methods:{
            getList:function(){
                this.spinShow = true;
                axios.get('/api/live', {
                    params:{
                        memberId:this.selectedMember[2],
                        limit:this.limit
                    }
                }).then(res =>{
                    if(res.data.errorCode == 0){
                        this.liveList = res.data.data.liveList.map(item =>{
                            item.cover = Tools.pictureUrls(item.picPath)[0];
                            item.date = new Date(item.startTime).format('yyyy-MM-dd hh:mm');
                            item.team.team_name = item.team.team_name.replace('TEAM ', '');
                            return item;
                        });
                        this.liveTotal = this.liveList.length;

                        this.reviewList = res.data.data.reviewList.map(item =>{
                            item.cover = Tools.pictureUrls(item.picPath)[0];
                            item.date = new Date(item.startTime).format('yyyy-MM-dd hh:mm');
                            item.team.team_name = item.team.team_name.replace('TEAM ', '');
                            return item;
                        });

                        this.reviewTotal = this.reviewList.length;

                        this.onLivePageChange(1);
                        this.onReviewPageChange(1);
                        this.spinShow = false;
                    }else{
                        console.log(res.data.msg);
                        this.spinShow = false;
                    }
                }).catch(error =>{
                    this.spinShow = false;
                    console.log(error);
                });
            },
            onLivePageChange:function(page){
                this.livePage = page;
                const start = (page - 1) * this.pageSize;
                this.currentLiveList = this.liveList.slice(start, start + this.pageSize);
            },
            onReviewPageChange:function(page){
                this.reviewPage = page;
                const start = (page - 1) * this.pageSize;
                this.currentReviewList = this.reviewList.slice(start, start + this.pageSize);
            },
            getUrl:function(item){
                if(item.streamPath.includes('.flv') || item.streamPath.includes('.mp4')){
                    return '/#/flvjs/' + item.liveId;
                }else if(item.streamPath.includes('.m3u8')){
                    return '/#/videojs/' + item.liveId;
                }else{
                    return '/#/';
                }
            },
        }
    }
</script>

<style scoped>
    .cover {
        width: 100%;
    }

    .layout-footer-center {
        text-align: center;
    }

    .cascader {
        display: inline-flex;
        min-width: 240px;
    }

    .header{
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .ivu-layout-header{
        padding: 0 32px;
    }
</style>

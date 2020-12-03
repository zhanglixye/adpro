<template>
    <v-card class="height_100">
        <!--项目标题输入区-->
        <div class="project-title-box pa-6">
            <h1 class="project-title">メールから以下の項目を入力してください</h1>
            <div>
                <span class="project">クライアント名：</span>
                <input class="project-input col-6" type="text" v-model="client_name" :disabled="accomplish">
            </div>
        </div>
        <!--素材内容区-->
        <abbey-list-table
            @getMsg="setMsg"
            ref="abbeyListTable"
            :initData="initData"
            :loadingDisplay="loadingDisplay"
            :projectListMore="business_flag_show"
            :formData="formData"
            :accomplish="accomplish"
            @errInformPopup="errInformPopup"
        ></abbey-list-table>
        <!--共通底部-->
        <div class="ma-0 justify-space-between pa-lr-6">
            <div v-show="!accomplish">
                <v-btn @click="disposeData" color="success" class="mr-3" style="width: 100px">処理する</v-btn>
                <v-btn @click="retainData" color="warning" class="mr-3" style="width: 100px">保留</v-btn>
                <UnKown
                    ref="unKown"
                    :unKnowShow="true"
                    :initData="initData"
                    :formData="formData"
                    :loadingDisplay="loadingDisplay"
                ></UnKown>
            </div>
            <div v-show="accomplish"></div>
            <date-buss-t :disabled_state="accomplish" ref="dateBuss"></date-buss-t>
        </div>
        <!--页面弹窗-->
        <popup-inform ref="popupInform" :message="message"></popup-inform>
        <popup-err-inform ref="popupErrInform" :message="message"></popup-err-inform>
        <confirm-dialog ref="confirmDialog"></confirm-dialog>
    </v-card>
</template>

<script>
import DateBussT from '../../../../Atoms/Biz/Common/DateBussT';
import AbbeyListTable from './AbbeyListTable';
import PopupInform from './popupInform'
import PopupErrInform from './popupErrInform'
import UnKown from './UnKown'
import ConfirmDialog from '../../../../Atoms/Dialogs/ConfirmDialog'
export default {
    name: 'AbbeyList',
    components:{
        PopupErrInform,
        PopupInform,
        UnKown,
        DateBussT,
        AbbeyListTable,
        ConfirmDialog
    },
    props:{
        business_flag_show:Boolean,
        initData: { type: Object, require: true },
        loadingDisplay: { type: Function, require: true },
        formData: { type: FormData, require: true },
        accomplish:Boolean
    },
    data(){
        return {
            message:'', //通知弹窗的消息
            client_name:''
        }
    },
    computed:{

    },
    created(){
        this.getWorkingHours();
    },
    mounted(){
        this.client_name = JSON.parse(this.initData.task_result_info.content).client_name;
    },
    methods:{
        //设置弹窗消息
        informPopup:function (msg) {
            this.message = msg;
            this.$refs.popupInform.dialog = true;
        },
        errInformPopup:function (msg) {
            this.message = msg;
            this.$refs.popupErrInform.dialog = true;
        },
        //向父级组件传递，子级组件传递过来的值（中转站），作用【列表模块】与【内容详细模块的切换】
        setMsg:function (data) {
            this.$emit('setInfoMsg',data);
        },
        //向子级组件传递，父级组件传递过来的值
        getMsg:function () {
            this.$refs.abbeyListTable.loadResultThumbnail()
        },
        //保留接口调用
        async retainData () {
            try {
                this.loadingDisplay(true);
                var _this = this;
                var url = '/api/biz/b00007/s00013/saveWork';
                var parameter = this.formData;
                parameter.set('task_result_content',JSON.stringify(this.$parent.getAllComponentData()));
                await axios.post(url,parameter).then((res)=>{
                    if (res.data.result == 'success'){
                        _this.informPopup('保存しました。');
                        _this.getWorkingHours();
                    } else {
                        _this.errInformPopup(res.data.err_message);
                    }
                })
            } catch (err) {
                console.log(err);
            } finally {
                this.loadingDisplay(false);
                this.dialog = false;
            }
        },
        //处理接口调用
        async disposeData () {
            try {
                //获取压缩包数组
                var zip_arr = this.$refs.abbeyListTable.$refs.popupZip.projectList;
                //声明处理函数的控制变量
                var UnZip_State;
                //循环查询压缩包数组的解压缩状态，如果有没加压的压缩包就结束循环，并给 UnZip_State 赋值 false
                var _this = this;
                var url = '/api/biz/b00007/s00013/commitWork';
                var parameter = this.formData;
                parameter.set('task_result_content',JSON.stringify(this.$parent.getAllComponentData()));
                for (var i = 0; i < zip_arr.length; i++) {
                    if (zip_arr[i].unzip_flag === false) {
                        UnZip_State = false;
                        break;
                    } else {
                        UnZip_State = true;
                    }
                }
                if (UnZip_State === false){
                    if (await (this.$refs.confirmDialog.show('未解凍ファイルはあります。続行しませんか', 'はい'))) {
                        this.loadingDisplay(true);
                        await axios.post(url,parameter).then((res)=>{
                            if (res.data.result == 'success'){
                                window.onbeforeunload = null;
                                window.location.href = '/tasks';
                            } else {
                                this.loadingDisplay(false);
                                _this.errInformPopup(res.data.err_message);
                            }
                        })
                    } else {
                        console.log('取消了处理操作');
                    }
                } else {
                    this.loadingDisplay(true);
                    await axios.post(url,parameter).then((res)=>{
                        if (res.data.result == 'success'){
                            window.onbeforeunload = null;
                            window.location.href = '/tasks';
                        } else {
                            this.loadingDisplay(false);
                            _this.errInformPopup(res.data.err_message);
                        }
                    })
                }
            } catch (err) {
                console.log(err);
            } finally {
                this.dialog = false;
            }
        },
        //后台返回后 转换时区
        convertToLocalTime: function (utcDate, outPutFormat='YYYY/MM/DD HH:mm') {
            return  moment.utc(utcDate).local().format(outPutFormat)
        },
        //获取后台作业时间,重新调用work_time接口
        async getWorkingHours () {
            let url = '/api/biz/common/mail/getWorktime';
            const workTimeResultN = await axios.post(url,this.formData);
            if (workTimeResultN.data.result == 'success'){
                // 中间部分默认开始时间  取task_id第一条数据的created_at
                let time_work_new =  workTimeResultN.data.data;
                let startDateCenter = time_work_new.created_at==null?'':time_work_new.created_at.date.substring(0,10);
                let startTimeCenter = time_work_new.created_at==null?'':time_work_new.created_at.date.substring(11,16);
                this.$refs.dateBuss.$data.system_start_date = startDateCenter;
                this.$refs.dateBuss.$data.system_start_time = startTimeCenter;
                //设置默认开始时间
                if (time_work_new.started_at == null) {
                    this.$refs.dateBuss.$data.user_start_date = '';
                    this.$refs.dateBuss.$data.user_start_time = '';
                } else {
                    let started_time = time_work_new.started_at;
                    let startDate = started_time.split(' ')[0];
                    let startTime = started_time.split(' ')[1].substr(0, 5);
                    this.$refs.dateBuss.$data.user_start_date = startDate;
                    this.$refs.dateBuss.$data.user_start_time = startTime;
                    //savedRealTime赋值
                    this.$refs.dateBuss.$data.savedRealTime.user_start_date = startDate;
                    this.$refs.dateBuss.$data.savedRealTime.user_start_time = startTime;
                }
                // //设置默认结束时间
                if (time_work_new.finished_at == null) {
                    this.$refs.dateBuss.$data.user_end_date = '';
                    this.$refs.dateBuss.$data.user_end_time = '';
                } else {
                    let finished_time = time_work_new.finished_at;
                    let finishDate = finished_time.split(' ')[0];
                    let finishTime = finished_time.split(' ')[1].substr(0, 5);
                    this.$refs.dateBuss.$data.user_end_date = finishDate;
                    this.$refs.dateBuss.$data.user_end_time = finishTime;
                    //savedRealTime赋值
                    this.$refs.dateBuss.$data.savedRealTime.user_end_date = finishDate;
                    this.$refs.dateBuss.$data.savedRealTime.user_end_time = finishTime;
                }
                this.$refs.dateBuss.$data.user_timekeeping = time_work_new.work_time == null?'0':Number(time_work_new.work_time * 60).toFixed(0);
                //remark赋值
                this.$refs.dateBuss.$data.remark = time_work_new.work_time_comment;
                //savedRealTime赋值
                this.$refs.dateBuss.$data.savedRealTime.user_timekeeping = time_work_new.work_time == null?'0':Number(time_work_new.work_time * 60).toFixed(0);
                this.$refs.dateBuss.$data.savedRealTime.remark = time_work_new.work_time_comment;
            }
        }
    }
}
</script>

<style scoped>
    .pa-6{
        padding: 24px;
    }
    .v-btn{
        margin: 0;
    }
    .col-6{
        max-width: 50%;
        min-width: 50%;
    }
    .pa-lr-6{
        padding-left:24px;
        padding-right: 24px;
    }
    .height_100{
        height: 100%;
    }
    .justify-space-between{
        display: flex;
        padding-bottom: 20px;
    }
    .project-title-box{
        border-bottom: solid 1px rgba(215, 215, 215, 1);
    }
    .project-title-box .project-title{
        font-size: 18px;
        font-weight: bold;
        color: #555555;
        margin-top: 0;
        margin-bottom: 20px;
    }
    .project-title-box .project{
        font-size: 14px;
        color: #555555;
        display: inline-block;
        height: 30px;
        line-height: 30px;
    }
    .project-input{
        font-size: 14px;
        outline: none;
        color: #333333;
        height: 30px;
        line-height: 30px;
        padding: 0 10px;
        margin-left: 6px;
        border-bottom: solid 1px rgba(215, 215, 215, 1);
    }
    button.success {
        background-color: #4db6ac!important;
        border-color: #4db6ac!important;
    }
    button.warning {
        background-color: #fb8c00!important;
        border-color: #fb8c00!important;
    }
</style>

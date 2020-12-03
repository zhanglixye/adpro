<template>
    <div class="px-3">
        <div class="top-link-box">
            <a @click="showPdfLink">マニュアルを参照する</a>
        </div>
        <div class="top-title">タイムライン</div>
        <v-checkbox
            class="post-letter"
            v-model="C00400_2"
            label="配信なし"
            hide-details
            :disabled="accomplish"
        />
        <line-table
            ref="lineTable"
            :accomplish="accomplish"
            :getThumbnail="getThumbnail"
        />
        <div class="bottom-function-box">
            <div v-show="!accomplish" style="float: left">
                <UnKown
                    ref="unKown"
                    :unKnowShow="true"
                    :initData="initData"
                    :axiosFormData="axiosFormData"
                    :loadingDisplay="loadingDisplay"
                    @unKnowEvent="unKnowEvent"
                ></UnKown>
                <v-btn
                    color="warning"
                    class="ma-0 mr-3"
                    style="width: 100px"
                    @click="saveWork"
                >保留</v-btn>
                <v-btn
                    color="success"
                    class="ma-0 mr-3"
                    style="width: 100px"
                    @click="disposeWork"
                >処理する</v-btn>
            </div>
            <div style="float: right">
                <date-buss-t
                    ref="dateBuss"
                    :disabled_state="accomplish"
                />
            </div>
        </div>
        <file-preview-dialog
            ref="file_Preview_Dialog"
            :isWide=true
        />
    </div>
</template>

<script>
import LineTable from './LineTable';
import UnKown from './UnKown'
import DateBussT from '../../../../Atoms/Biz/Common/DateBussT';
import FilePreviewDialog from '../../../../Atoms/Dialogs/FilePreviewDialog';
export default {
    name: 'LineAcquireInformationList',
    components: {
        LineTable,
        UnKown,
        DateBussT,
        FilePreviewDialog
    },
    props: {
        initData: {type: Object, require: true},
        edit: {type: Boolean, require: false, default: false},
        loadingDisplay: {type: Function, require: true},
        axiosFormData: {type: Function, require: true},
        convertToTime: {type: Function, require: true},
        convertToLocalTime: {type: Function, require: true},
        getThumbnail: {type: Function, require: true},
        accomplish: {type: Boolean, default: false}
    },
    data: () => ({
        C00400_2: false
    }),
    mounted(){
        //组件加载后获取后台作业时间
        this.getWorkingHours();
    },
    methods: {
        //保留接口
        async saveWork() {
            try {
                this.loadingDisplay(true);
                let _this = this;
                let url = '/api/biz/b00017/s00023/saveWork';
                let parameter = this.axiosFormData();
                await axios.post(url, parameter).then((res) => {
                    if (res.data.result == 'success'){
                        _this.$parent.$parent.$refs.alert.show('保存しました。');
                        _this.getWorkingHours();
                    } else {
                        _this.$parent.$parent.$refs.alert.show(res.data.err_message);
                    }
                })
            } catch (err) {
                console.error(err)
            } finally {
                this.loadingDisplay(false)
            }
        },
        //处理接口调用
        async disposeWork () {
            try {
                this.loadingDisplay(true);
                let _this = this;
                let url = '/api/biz/b00017/s00023/commitWork';
                let parameter = this.axiosFormData();
                await axios.post(url,parameter).then((res)=>{
                    if (res.data.result == 'success'){
                        window.onbeforeunload = null;
                        window.location.href = '/tasks';
                    } else {
                        _this.$parent.$parent.$refs.alert.show(res.data.err_message);
                    }
                })
            } catch (err) {
                console.error(err);
            } finally {
                this.loadingDisplay(false);
            }
        },
        //不明处理
        async unKnowEvent() {
            try {
                this.loadingDisplay(true);
                let _this = this;
                let url = '/api/biz/b00017/s00023/wrongWork';
                let parameter = this.axiosFormData();
                await axios.post(url, parameter).then((res) => {
                    if (res.data.result == 'success') {
                        window.onbeforeunload = null;
                        window.location.href = '/tasks';
                    } else {
                        _this.$parent.$parent.$refs.alert.show(res.data.err_message);
                    }
                })
            } catch (err) {
                console.error(err);
            } finally {
                this.loadingDisplay(false)
            }
        },
        //获取后台作业时间,重新调用work_time接口
        async getWorkingHours() {
            let url = '/api/biz/common/mail/getWorktime';
            let parameter = this.axiosFormData();
            await axios.post(url, parameter).then((res)=>{
                if (res.data.result == 'success') {
                    // 中间部分默认开始时间  取task_id第一条数据的created_at
                    let time_work_new =  res.data.data;
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
            });
        },
        //マニュアルを参照する链接
        showPdfLink(){
            let type = '';
            const file = {
                name: 'マニュアルを参照する.pdf',
                file_path: 'manuals/B00017/S00023/マニュアルを参照する.pdf',
            }
            this.$refs.file_Preview_Dialog.show([file], [], type);
        }
    }
}
</script>

<style scoped lang="scss">
/***
 * 重置CSS
 */
//修改 List 组件中的配信 checkbox 的文本位置
.post-letter ::v-deep .v-label {
    top: 1px;
}

/***
 * 组件内的样式
 */
.top-link-box {
    text-align: right;
    line-height: 36px;
    a {
        color: #4db6ac;
        display: inline-block;
        font-size: 12px;
        transform: translateY(2px);
    }
}
.top-title {
    font-size: 16px;
    color: #ffffff;
    text-align: center;
    line-height: 36px;
    font-weight: bold;
    background-color: #4db6ac;
}
.post-letter {
    margin: 15px 0;
}
.bottom-function-box {
    position: absolute;
    bottom: 22px;
    width: 100%;
    margin-left: -16px;
    padding: 0 16px;
}
.bottom-function-box::after {
    display: block;
    content: '';
    clear: both;
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

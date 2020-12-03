<template>
    <v-card class="business-box" :style="{height:height,position:'relative'}">
        <line-acquire-information-list
            ref="lineList"
            v-show="!businessState"
            :initData="initData"
            :edit="edit"
            :loadingDisplay="loadingDisplay"
            :axiosFormData="axiosFormData"
            :accomplish="accomplish"
            :convertToTime="convertToTime"
            :convertToLocalTime="convertToLocalTime"
            :getThumbnail="getThumbnail"
        />
        <line-acquire-information-input
            ref="lineInput"
            v-show="businessState"
            :initData="initData"
            :edit="edit"
            :loadingDisplay="loadingDisplay"
            :axiosFormData="axiosFormData"
            :getThumbnail="getThumbnail"
            :accomplish="accomplish"
            :projectInfo="projectInfo"
        />
        <alert-dialog
            ref="alert"
        />
        <confirm-dialog
            ref="confirm"
        />
    </v-card>
</template>

<script>
import LineAcquireInformationList from '../../../Organisms/Biz/B00016/S00022/LineAcquireInformationList';
import LineAcquireInformationInput from '../../../Organisms/Biz/B00016/S00022/LineAcquireInformationInput';
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog';
import ConfirmDialog from '../../../Atoms/Dialogs/ConfirmDialog';
export default {
    name: 'S00022',
    components: {
        LineAcquireInformationList,
        LineAcquireInformationInput,
        AlertDialog,
        ConfirmDialog
    },
    props: {
        initData: {type: Object, require: true},
        edit: {type: Boolean, require: false, default: false},
        loadingDisplay: {type: Function, require: true}
    },
    data: () => ({
        businessHeight: 640,
        businessState: false,
        projectInfo: null
    }),
    computed: {
        height: function () {
            return (this.businessHeight === 0) ? '100%' : this.businessHeight + 'px';
        },
        workState: function (){
            return JSON.parse(this.initData.task_result_info.content).results.type
        },
        accomplish() {
            return this.initData.task_info.status == 2 ? true : false;
        }
    },
    mounted(){
        //刷新页面时提示
        if (this.accomplish == false){
            window.onbeforeunload = () => {
                return '';
            };
        }
        //判断作业的状态，做不同的处理。-1 未开始，0 处理完了，1 不明完了，4 保存，3 勾选 [配信なし] 后的处理状态
        if (this.workState == 4){
            this.workInitialize();  //执行业务初始化
        } else if (this.workState == 0){
            this.workInitialize();  //执行业务初始化
        } else if (this.workState == 1){
            this.workInitialize();  //执行业务初始化
        } else if (this.workState == 3){
            this.workInitialize();  //执行业务初始化
        }
    },
    methods: {
        //业务初始化
        workInitialize(){
            this.$nextTick(function (){
                // JSON.parse(this.initData.task_result_info.content).data.G00000_3
                let line_table = this.$refs.lineList.$refs.lineTable;
                //获取表格数据
                let item_arr = [];
                //设置配信状态
                // console.log(JSON.parse(this.initData.task_result_info.content).data);
                this.$refs.lineList.C00400_2 = JSON.parse(this.initData.task_result_info.content).data.C00400_2;
                //设置表格数据
                JSON.parse(this.initData.task_result_info.content).data.G00000_3.forEach((item,index)=>{
                    item_arr.push({
                        indexVal: index,
                        itemCheck: false,
                        postLetterDateState: false,
                        C00700_4: item.C00700_4,
                        C00700_4_picker: (item.C00700_4 == null || '') ? null : item.C00700_4.replace(/\//g,'-'),
                        postLetterTimeState: false,
                        C00100_5: item.C00100_5,
                        C00200_8: item.C00200_8,
                        C00400_9: item.C00400_9,
                        C00400_10: item.C00400_10,
                        C00400_11: item.C00400_11,
                        C00400_12: item.C00400_12,
                        C00400_13: item.C00400_13,
                        C00400_14: item.C00400_14,
                        C00400_15: item.C00400_15,
                        C00400_16: item.C00400_16,
                        C00400_17: item.C00400_17,
                        C00800_6: item.C00800_6,
                        G00800_7: item.G00800_7
                    })
                });
                // console.log('item_arr',item_arr);
                line_table.G00000_3 = item_arr;
                line_table.setADThumbnail();
            });
        },
        //获取素材的缩略图
        async getThumbnail(url) {
            try {
                return await axios.get('/api/utilities/getFileReferenceUrlForThumbnail?file_path=' + url);
            } catch (err) {
                console.error(err);
                return {data: {status: false, message: '缩略图请求发送错误，报错反馈节点：getThumbnail'}};
            }
        },
        //创建请求的共通参数
        axiosFormData() {
            let formData = new FormData();
            let content = {};
            formData.append('step_id', this.initData['request_info']['step_id']);
            formData.append('request_id', this.initData['request_info']['request_id']);
            formData.append('request_work_id', this.initData['request_info']['id']);
            formData.append('task_id', this.initData['task_info']['id']);
            formData.append('task_started_at', this.initData['task_started_at']);
            content.C00400_2 = JSON.parse(JSON.stringify(this.$refs.lineList.C00400_2));
            content.G00000_3 = JSON.parse(JSON.stringify(this.$refs.lineList.$refs.lineTable.G00000_3));
            content.G00000_3.forEach((item)=>{
                delete item.C00700_4_picker;
                item.G00800_7.forEach((i)=>{
                    i.thumbnail = '';
                })
            });
            content.G00000_18 = {
                C00700_19: this.$refs.lineList.$refs.dateBuss.$data.user_start_date===''?null:this.$refs.lineList.$refs.dateBuss.$data.user_start_date + ' '+ this.$refs.lineList.$refs.dateBuss.$data.user_start_time+':00',
                C00700_20: this.$refs.lineList.$refs.dateBuss.$data.user_end_date===''?null:this.$refs.lineList.$refs.dateBuss.$data.user_end_date + ' ' + this.$refs.lineList.$refs.dateBuss.$data.user_end_time+':00',
                C00700_21: Number(this.$refs.lineList.$refs.dateBuss.$data.user_timekeeping/60).toFixed(2),
                C00700_22: this.$refs.lineList.$refs.dateBuss.$data.remark
            }
            content.comment = this.$refs.lineList.$refs.unKown.lastName;
            formData.append('task_result_content', JSON.stringify(content));
            return formData
        },
        //【前台】传给后台的时间转换方法
        convertToTime: function(date){
            if (date == ''){
                return null;
            }
            return moment(date).utc().format('YYYY/MM/DD HH:mm');
        },
        //【后台】传给前台的时间转换方法
        convertToLocalTime: function (date) {
            return moment.utc(date).local().format('YYYY/MM/DD HH:mm');
        }
    }
}
</script>

<style scoped lang="scss">
.business-box {
    position: relative;
}
.business-box::-webkit-scrollbar {
    display: none
}
</style>

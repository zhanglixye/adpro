<template>
    <div class="row ma-0 checkboxDialog" justify="center">
        <v-dialog v-model="dialog" scrollable width="650px" persistent>
            <template slot="activator">
                <v-tooltip bottom>
                    <template slot="activator">
                        <v-btn v-if="createRowNumberNodes == 0" color="#333333" flat fab small dark class="ma-0">
                            <svg width="20px" height="20px">
                                <path fill-rule="evenodd"  fill="rgb(25, 118, 210)"
                                      d="M11.000,19.000 L11.000,17.000 L20.000,17.000 L20.000,19.000 L11.000,19.000 ZM11.000,12.000 L20.000,12.000 L20.000,14.000 L11.000,14.000 L11.000,12.000 ZM11.000,6.000 L20.000,6.000 L20.000,8.000 L11.000,8.000 L11.000,6.000 ZM11.000,1.000 L20.000,1.000 L20.000,3.000 L11.000,3.000 L11.000,1.000 ZM0.000,11.000 L9.000,11.000 L9.000,20.000 L0.000,20.000 L0.000,11.000 ZM2.000,18.000 L7.000,18.000 L7.000,13.000 L2.000,13.000 L2.000,18.000 ZM0.000,0.000 L9.000,0.000 L9.000,9.000 L0.000,9.000 L0.000,0.000 ZM2.000,7.000 L7.000,7.000 L7.000,2.000 L2.000,2.000 L2.000,7.000 Z"/>
                            </svg>
                        </v-btn>
                    </template>
                    <span>チェックリスト</span>
                </v-tooltip>
                <v-tooltip bottom>
                    <template slot="activator">
                        <v-btn v-if="createRowNumberNodes == 1" color="#4db6ac" flat fab small dark class="ma-0">
                            <svg width="20px" height="20px">
                                <path fill-rule="evenodd"  fill="rgb(77, 182, 172)"
                                      d="M11.000,19.000 L11.000,17.000 L20.000,17.000 L20.000,19.000 L11.000,19.000 ZM11.000,12.000 L20.000,12.000 L20.000,14.000 L11.000,14.000 L11.000,12.000 ZM11.000,6.000 L20.000,6.000 L20.000,8.000 L11.000,8.000 L11.000,6.000 ZM11.000,1.000 L20.000,1.000 L20.000,3.000 L11.000,3.000 L11.000,1.000 ZM0.000,11.000 L9.000,11.000 L9.000,20.000 L0.000,20.000 L0.000,11.000 ZM2.000,18.000 L7.000,18.000 L7.000,13.000 L2.000,13.000 L2.000,18.000 ZM0.000,0.000 L9.000,0.000 L9.000,9.000 L0.000,9.000 L0.000,0.000 ZM1.000,8.000 L8.000,8.000 L8.000,1.000 L1.000,1.000 L1.000,8.000 ZM2.000,2.000 L7.000,2.000 L7.000,7.000 L2.000,7.000 L2.000,2.000 Z"/>
                            </svg>
                        </v-btn>
                    </template>
                    <span>チェックリスト</span>
                </v-tooltip>
                <v-tooltip bottom>
                    <template slot="activator">
                        <v-btn v-if="createRowNumberNodes == 2" color="#1976D2" flat fab small dark class="ma-0">
                            <svg
                                    width="20px" height="20px">
                                <path fill-rule="evenodd"  fill="rgb(153, 153, 153)"
                                      d="M11.000,19.000 L11.000,17.000 L20.000,17.000 L20.000,19.000 L11.000,19.000 ZM11.000,12.000 L20.000,12.000 L20.000,14.000 L11.000,14.000 L11.000,12.000 ZM11.000,6.000 L20.000,6.000 L20.000,8.000 L11.000,8.000 L11.000,6.000 ZM11.000,1.000 L20.000,1.000 L20.000,3.000 L11.000,3.000 L11.000,1.000 ZM0.000,11.000 L9.000,11.000 L9.000,20.000 L0.000,20.000 L0.000,11.000 ZM0.000,0.000 L9.000,0.000 L9.000,9.000 L0.000,9.000 L0.000,0.000 Z"/>
                            </svg>
                        </v-btn>
                    </template>
                    <span>チェックリスト</span>
                </v-tooltip>
            </template>
            <v-card>
                <v-card-title>
                    <v-spacer>
                        <div class="row no-gutters">
                            <div class="col-10 pa-0">
                                <div style="font-size: 16px">作業内容に問題が無いか確認してください</div>
                            </div>
                            <div class="col-2 pa-0" style="display: flex;flex-direction: row-reverse">
                                <div style="cursor: pointer">
                                    <v-icon @click="dialog = false">close</v-icon>
                                </div>
                            </div>
                        </div>
                    </v-spacer>
                </v-card-title>
                <v-divider></v-divider>
                <v-card-text>
                    <div v-for="(item_parent,i) in checkboxData" :key="i" :class="[checkboxListMade?'check_dia_border':'check_dia_border_none',checkbox_title_border,(item_parent.pos == 1)?'ml-au30':'']">
                        <div v-show="!checkboxListMade">{{item_parent.content}}</div>
                        <v-flex :class="[!checkboxListMade?'flex-wrap':'']">
                            <component
                                    :rules="!checkboxListMade&&(item.component_type == 1)?rules:[]"
                                    v-on:change="dealTextChange"
                                    :allow_file_types="allow_file_types"
                                    :emit_message="file_upload_emit_message"
                                    :is="diffComponentShowCheck(item)"
                                    :items="item.items"
                                    item-text="content"
                                    item-value="value"
                                    v-model="item.value"
                                    v-for="(item,o) in item_parent.items"
                                    :key="o" class="fw"
                                    :label="item.content"
                                    :disabled="item.disabled"
                                    ref="checkboxes"
                            ></component>
                        </v-flex>
                        <div class="ming" v-show="checkboxListMade">{{item_parent.content}}</div>
                        <v-radio-group v-if="!checkboxListMade && (item_parent.group_component_type == 1)" row v-model="item_parent.value" @change="handleRadio">
                            <v-radio
                                    class="mr-10 fw"
                                    v-for="(item,o) in item_parent.items"
                                    :key="o"
                                    :label="item.content"
                                    :value="radioArray[o]"
                            >
                            </v-radio>
                        </v-radio-group>
                    </div>
                </v-card-text>
                <v-card-actions>
                    <v-btn
                            color="success"
                            @click="dialog = false"
                            style="margin:0 auto;width: 100px"
                    >
                        確定
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <file-preview-dialog ref="filePreviewDialog" :isWide=true></file-preview-dialog>
        <alert-dialog ref="alert"></alert-dialog>
    </div>
</template>

<script>
import AExtend from '../../../../Organisms/Biz/Common/DynamicPage/AExtend';
import VRadioExtend from '../../../../Organisms/Biz/Common/DynamicPage/VRadioExtend';
import FilePreviewDialog from '../../../Dialogs/FilePreviewDialog';
import AlertDialog from '../../../Dialogs/AlertDialog';
import VDatePickerExtend from '../../../../Organisms/Biz/Common/DynamicPage/VDatePickerExtend';
import FileUpload from '../../../../Atoms/Biz/Common/FileUpload';
export default {
    components: {FileUpload, VDatePickerExtend, AlertDialog, FilePreviewDialog, VRadioExtend, AExtend},
    props:{
        axiosFormData:{type:FormData,require:true},
        ajaxPath:String,
        checkboxListMade:{type:Boolean,require: false,default:true},
        loadingDisplay: { type: Function, require: true },
    },
    data(){
        return {
            dialog: false,
            //源数据
            checkboxData:[],
            //保存过的数据回显，重新赋值checkedArray 提交数据[1,2,3,4]
            checkboxValue:[],
            //commit array
            commitArray:[],
            //checkbox选择数量flag
            createRowNumberNodes:0,
            //new demand
            //是否开启半角验证
            match : true,
            //class name
            mt4:'mt-4',
            checkbox_title_border:'check_dia_bo',
            //完了 不要
            radioArray:[0,1],
            file_upload_emit_message: 'email-sale-file',
            //允许文件类型
            allow_file_types: ['application/pdf'],
            //文件集合
            uploadFiles:[],
            //判断文件是否改动
            fileChangeFlag:false,
            //checkbox file content
            checkboxFileStr:'',
            clearFlag:false,
        }
    },
    computed:{
        //半角验证
        rules(){
            const rules = [];
            if (this.match) {
                const rule =
                    // eslint-disable-next-line no-control-regex
                    v => (/^(-?\d+)(\.\d+)?$/g.test(v) || !v) || '半角文字を入力してください';
                rules.push(rule)
            }
            return rules;
        },
    },
    created(){
        /**
         * 文件drop事件监听
         * @type {default}
         */
        //const self = this;
        //防止多次监听
        // eventHub.$off(this.file_upload_emit_message);
        // // ファイルアップロード用
        // eventHub.$on(this.file_upload_emit_message, function(data){
        //     //初始化设置uploadFiles为空数组
        //     self.uploadFiles = [];
        //     // check_filesに追加
        //     for (const file of data.file_list){
        //         const result = {
        //             err_description: '',
        //             file_name: file.name,
        //             file_size: file.size,
        //             file_data: file.data,
        //             file_path: '',
        //             type: file.type,
        //         };
        //         self.uploadFiles.push(result);
        //         self.fileChangeFlag = true;
        //     }
        //     // ローカルに一時保存
        //     self.moveToTemporary(self.uploadFiles);
        // });
    },
    watch:{
        checkboxData(){
            if (this.checkboxValue.length > 0){
                this.checkboxData = this.checkboxValue;
            }
            this.getFileInformationCheckbox();
            //默认icon赋值
            this.dealRowNumberNodes();
        },
    },
    mounted(){

    },
    updated(){
        if (this.clearFlag){
            let indexNumber;
            this.$refs.checkboxes.forEach((value,index)=>{
                if (value.$el.className == 'fw -undrag'){
                    indexNumber = index;
                }
            });
            //refs 不是响应式的 数据更新到 DOM 的阶段才能获取响应的ref
            this.$refs.checkboxes[indexNumber].$data.dragDropFileName = this.checkboxFileStr;
            this.$refs.checkboxes[indexNumber].$data.clearFlag = this.clearFlag;
        }
    },
    methods:{
        dealTextChange(value){
            if (typeof value == 'string') {
                eventHub.$emit('checkbox-text',{
                    'message': value
                })
            }
            this.dealRowNumberNodes();
        },
        dealRowNumberNodes(){
            //AG业务判断
            if (this.checkboxListMade){
                let arrTrue = [];
                let arrFalse = [];
                let sum = 0;
                this.checkboxData.forEach( (item) => {
                    item.items.forEach((i)=>{
                        if (i.component_type == 400 || i.component_type == 500 || i.component_type == 100){
                            sum ++;
                            if (typeof i.value == 'string' || typeof i.value == 'number'){
                                if (i.value != ''){
                                    arrTrue.push(i.value);
                                } else {
                                    arrFalse.push(i.value);
                                }
                            }
                            if (typeof i.value == 'boolean'){
                                if (i.value){
                                    arrTrue.push(i.value);
                                } else {
                                    arrFalse.push(i.value);
                                }
                            }
                            if (i.value == null){
                                arrFalse.push(i.value);
                            }
                        }
                    })
                })
                if (sum == arrTrue.length){
                    this.createRowNumberNodes = 2;
                } else if (sum == arrFalse.length){
                    this.createRowNumberNodes = 0;
                } else {
                    this.createRowNumberNodes = 1;
                }
            } else {
                this.iconFlagAg();
            }
        },
        diffComponentShowCheck(item){
            if (item.component_type == 0 || item.component_type == 400){
                return 'v-checkbox';
            } else if (item.component_type == 1 || item.component_type == 100) {
                return 'v-text-field';
            } else if (item.component_type == 1000){
                return 'a-extend';
            } else if (item.component_type == 300){
                return 'v-select';
            } else if (item.component_type == 500){
                return 'v-radio-extend';
            }  else if (item.component_type == 700){
                return 'v-date-picker-extend';
            } else if (item.component_type == 800){
                return 'file-upload';
            } else {
                return null;
            }
        },
        //不要时 status  disabled
        handleRadio(value){
            if (value === 1){
                this.checkboxData.forEach((item)=>{
                    if (item.order_num === 112 || item.order_num === 126) {
                        item.items.forEach(i=>{
                            i.disabled = true;
                            i.value=false;
                        })
                    }
                })
            } else {
                this.checkboxData.forEach((item)=>{
                    if (item.order_num === 112 || item.order_num === 126) {
                        item.items.forEach(i=>{
                            i.disabled = false;
                        })
                    }
                })
            }
            this.iconFlagAg();
        },
        //AG icon status 判断
        iconFlagAg(){
            //为[]的判断
            if (this.checkboxData.length == 0){
                this.createRowNumberNodes = 0;
                return;
            }
            let sumAg = 0;
            if (this.checkboxData[0]['items'][0].value !== null && this.checkboxData[0]['items'][0].value != ''){
                sumAg ++;
            }
            if (this.checkboxData[1].value == 0){
                //完了
                try {
                    this.checkboxData[2]['items'].forEach(i =>{
                        if (i.value){
                            sumAg ++;
                            throw new Error('exit');
                        }
                    })
                } catch (e) {
                    //console.log(e)
                }
                if (this.checkboxData[3]['items'][0].value){
                    sumAg ++;
                }
            } else if (this.checkboxData[1].value == 1) {
                //不要
                sumAg +=2;
            }
            if (this.checkboxData[4]['items'][0].value){
                sumAg ++;
            }
            if (sumAg == 4) {
                this.createRowNumberNodes = 2;
            } else if (sumAg == 0){
                this.createRowNumberNodes = 0;
            } else {
                this.createRowNumberNodes = 1;
            }
        },
        //点击file下载文件
        clickDownload(data){
            let self = this;
            let base64 = '';
            let fileNewName = '';
            let file_path = '';
            let type = '';
            for (const v of self.uploadFiles){
                if (v.file_name == data){
                    base64 = v.file_data;
                    fileNewName = v.file_name;
                    file_path = v.file_path;
                    type = v.type;
                }
            }
            if (file_path == '') {
                self.downLoadPdf(base64,self.splitFileName(fileNewName),self.getFileType(fileNewName));
                return
            }
            const file = {
                name: fileNewName,
                file_path: file_path,
            }
            self.$refs.filePreviewDialog.show([file], [], type)
        },
        //下载文件
        async downLoadPdf(baseData, firstFileName, lastFileName) {
            let fileName = firstFileName + '.' + lastFileName;
            //没有file_sep 发请求
            if (baseData === undefined){
                for (const i of this.uploadFiles){
                    if (i.file_name == fileName && i.file_seq_no !== undefined){
                        let formData = new FormData();
                        let obj = {
                            'file_seq_no':i.file_seq_no
                        }
                        formData.append('step_id',this.axios.get('step_id'));
                        formData.append('request_id',this.axios.get('request_id'));
                        formData.append('request_work_id',this.axios.get('request_work_id'));
                        formData.append('task_id',this.axios.get('task_id'));
                        formData.append('task_started_at',this.axios.get('task_started_at'));
                        formData.append('file_seq_no',obj.file_seq_no);
                        formData.append('task_result_content',this.axios.get('task_result_content'));
                        let res = await axios.post(this.ajaxPath+'downloadFile',formData)
                        if (res.data.result == 'success'){
                            baseData = res.data.data.file_data
                            //console.log(baseData)
                        } else {
                            this.$refs.alert.show(res.data.err_message);
                        }
                    }
                }
            }
            let bytes = atob(baseData.substring(baseData.indexOf(',') + 1)); //去掉url的头，并转换为byte
            //处理异常,将ascii码小于0的转换为大于0
            let content = new ArrayBuffer(bytes.length);
            let ia = new Uint8Array(content);
            for (let i = 0; i < bytes.length; i++) {
                ia[i] = bytes.charCodeAt(i);
            }
            let blob;
            if (lastFileName == 'pdf') {
                blob = new Blob([content], {
                    'type': 'application/pdf'
                });
            } else if (lastFileName == 'zip') {
                blob = new Blob([content], {
                    'type': 'application/zip'
                });
            } else if (lastFileName == 'txt'){
                blob = new Blob([ia], {
                    'type': 'text/plain,charset=shift-jis'
                });
            } else if (lastFileName == 'log'){
                blob = new Blob([ia], {
                    'type': 'text/plain'
                });
            } else if (lastFileName == 'xls'){
                blob = new Blob([content], {
                    'type': 'application/vnd.ms-excel'
                });
            } else {
                blob = new Blob([content], {
                    'type': 'application/excel'
                });
            }

            if (window.navigator.msSaveBlob) {
                window.navigator.msSaveBlob(blob, fileName);
                // msSaveOrOpenBlobの場合はファイルを保存せずに開ける
                window.navigator.msSaveOrOpenBlob(blob, fileName);
            } else {
                let itemA = document.createElement('a');
                itemA.href = window.URL.createObjectURL(blob);
                itemA.download = fileName;
                itemA.click();
            }
        },
        //file update
        fileUpdate(str){
            this.$nextTick(()=>{
                this.checkboxFileStr = str;
                this.clearFlag = true;
            })
        },
        // Customer provided
        moveToTemporary: async function (uploadFile) {
            this.loadingDisplay(true);
            // 画像データをblobURL -> base64
            // const file = this.uploadFile
            const files = uploadFile;
            const convert = this.convertToBase64;
            console.log('promise all start')
            await Promise.all(files.map(async upload_file => upload_file = await convert(upload_file)))
            console.log('promise all end')
            for (let i = 0; i <  uploadFile.length; i++){
                delete uploadFile[i].type
            }
            //await convert(files[0]);
            //支持多文件上传
            //await [].forEach.call(files,convert);
            //调用uploadFileToS3接口
            let formData = new FormData();
            formData.append('step_id',this.axiosFormData.get('step_id'));
            formData.append('request_id',this.axiosFormData.get('request_id'));
            formData.append('request_work_id',this.axiosFormData.get('request_work_id'));
            formData.append('task_id',this.axiosFormData.get('task_id'));
            formData.append('task_started_at',this.axiosFormData.get('task_started_at'));
            formData.append('task_result_file_content',JSON.stringify(uploadFile));
            axios.post('/api/biz/common/dynamic_page/uploadFileToS3',formData)
                .then((res) => {
                    if (res.data.result == 'success'){
                        //获取seq-no
                        let seq_arr = [];
                        let file_seq_array = res.data.data;
                        file_seq_array.forEach((item)=>{
                            seq_arr.push(item.seq_no);
                        })
                        //将seq_arr 传给 数组
                        this.checkboxData.forEach((item) => {
                            if (item.items[0].component_type == 800) {
                                item.value = seq_arr;
                            }
                        });
                        //赋值uploadFiles
                        this.uploadFiles = [];
                        for (const value of file_seq_array){
                            this.uploadFiles.push({
                                'file_name':value.file_name,
                                'file_path':value.file_path,
                                //暂时不设置
                                'file_seq_no':value.seq_no,
                            })
                        }
                    }
                })
                .catch(error =>{
                    console.log(error)
                })
                .finally(()=>{
                    this.loadingDisplay(false);
                })
        },
        //To base64
        convertToBase64: function(file){
            return new Promise((resolve, reject) => {
                if ('data' == file.file_data.substring(0, 4)) resolve(file)
                var xhr = new XMLHttpRequest()
                xhr.responseType = 'blob';
                xhr.onload = () => {
                    var reader = new window.FileReader()
                    reader.readAsDataURL(xhr.response)
                    reader.onloadend = () => {
                        // メモリから削除
                        URL.revokeObjectURL(file.file_data)
                        file.file_data = reader.result
                        resolve(file)
                    }
                    reader.onerror = (e) => reject(e)
                }
                xhr.onerror = (e) => reject(e)
                xhr.open('GET', file.file_data)
                xhr.send()
            })
        },
        //请求getTaskResultFileInfoById
        async getFileInformationCheckbox() {
            try {
                this.loadingDisplay(true);
                let formData = new FormData();
                let seq_no_array;
                //将seq_arr作为参数
                this.checkboxData.forEach((item) => {
                    if (item.items[0].component_type == 800) {
                        seq_no_array = item.value;
                    }
                });
                let obj = {
                    task_result_id:this.axiosFormData.get('id'),
                    seq_no_array:seq_no_array,
                };
                formData.append('step_id',this.axiosFormData.get('step_id'));
                formData.append('request_id',this.axiosFormData.get('request_id'));
                formData.append('request_work_id',this.axiosFormData.get('request_work_id'));
                formData.append('task_id',this.axiosFormData.get('task_id'));
                formData.append('task_started_at',this.axiosFormData.get('task_started_at'));
                formData.append('task_result_file_content',JSON.stringify(obj));
                const res = await axios.post('/api/biz/common/dynamic_page/getTaskResultFileInfoById',formData)
                if (res.data.result == 'success'){
                    let contentFile = res.data.data;
                    //file
                    let fileString = '';
                    this.uploadFiles = [];
                    for (const value of contentFile){
                        this.uploadFiles.push({
                            'file_name':value.file_name,
                            'file_path':value.file_path,
                            //暂时不设置
                            'file_seq_no':value.seq_no,
                        })
                        fileString += ('<span>'+ value.file_name + '</span>' + ',');
                    }
                    fileString = fileString.substring(0,fileString.length - 1);
                    if (fileString != ''){
                        this.fileUpdate(fileString);
                    }
                }
            } catch (e) {
                this.loadingDisplay(false);
                console.log(e);
                this.$refs.alert.show(Vue.i18n.translate('common.message.internal_error'));
            } finally {
                this.loadingDisplay(false);
            }
        }
    }
}
</script>

<style scoped lang="scss">
    .v-card__actions{
        padding: 18px !important;
    }
    .htu{
        padding-left: 50px;
        margin-top: 5px;
    }
    .fw{
        font-weight: bold;
        padding-left: 50px;padding-right: 50px
    }
    .flex-wrap .fw{
        padding-left: 20px;
        padding-right: 20px;
        flex: unset;
    }
    .flex-wrap .fw:nth-child(2){
        //padding-left: 0;
    }
    .flex-wrap .fw:nth-child(5){
        //padding-left: 55px;
    }
    .flex-wrap .fw:nth-child(6){
        padding-left: 12px;
    }
    .check_dia_bo{
        position: relative;
        padding:20px 0;
        .ming{
            background-color: white;
            position: absolute;
            left: 30px;
            top: -15px;
            color: #aaaaaa;
            font-size: 18px
        }
    }
    .check_dia_border{
        border: 1px solid rgba(0,0,0,0.12);
        margin-top: 24px;
    }
    .check_dia_border_none{
        border: none;
        padding: 0 !important;
    }
    .ml-au30{
        margin-left: 30px !important;
    }
    /*new add vuetify 降级*/
    hr{
        margin: 0;
    }
    /*v-row -> .row v-col -> .col */
    .row {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
        -webkit-box-flex: 1;
        -ms-flex: 1 1 auto;
        flex: 1 1 auto;
        margin-right: -12px;
        margin-left: -12px;
    }
    /*button style overwrite*/
    button.success {
        background-color: #4db6ac!important;
        border-color: #4db6ac!important;
    }
    .checkboxDialog{
        display: flex;
        justify-content: center;
        width: 100px;
    }
    .flex-wrap{
        display: flex;
        flex-wrap: wrap;
    }
</style>

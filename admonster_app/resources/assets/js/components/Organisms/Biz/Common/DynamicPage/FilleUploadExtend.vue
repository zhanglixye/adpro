<template>
    <v-layout class="ma-2">
        <v-flex style="display: flex">
            <div class="pr-3" v-for="(item,i) in selectFiles" :key="i">
                              <span class="mr-3">
                                <v-icon style="cursor: pointer">description</v-icon>
                              </span>
                <a
                        @click.stop.ctrl="pdfPreviewCommon(item.file_path,item.file_seq_no,item.file_name,'tab')"
                        @click.stop.exact="pdfPreviewCommon(item.file_path,item.file_seq_no,item.file_name)"
                        @click.stop.shift="pdfPreviewCommon(item.file_path,item.file_seq_no,item.file_name,'window')"
                >{{item.file_name}}</a>
            </div>
        </v-flex>
        <file-preview-dialog ref="filePreviewDialog" :isWide=true></file-preview-dialog>
    </v-layout>
</template>

<script>
import FilePreviewDialog from '../../../../Atoms/Dialogs/FilePreviewDialog';
export default {
    name: 'FilleUploadExtend',
    components: {FilePreviewDialog},
    props:{
        selectFiles:{type:Array,require: true}
    },
    methods:{
        //下载文件
        async download(file_seq_no,name){
            //判断是否是主动选择文件
            if (file_seq_no === undefined){
                this.data.uploadFiles.forEach((value)=>{
                    let fileName = value.file_name;
                    if (fileName == name){
                        let baseData = value.file_data;
                        this.downLoadPdf(baseData,this.splitFileName(name),this.getFileType(name));
                    }
                });
            } else {
                let baseData;
                let formData = new FormData();
                formData.append('step_id',this.axiosFormData.get('step_id'));
                formData.append('request_id',this.axiosFormData.get('request_id'));
                formData.append('request_work_id',this.axiosFormData.get('request_work_id'));
                formData.append('task_id',this.axiosFormData.get('task_id'));
                formData.append('task_started_at',this.axiosFormData.get('task_started_at'));
                formData.append('file_seq_no',file_seq_no);
                formData.append('task_result_content',this.axiosFormData.get('task_result_content'));
                let res = await axios.post(this.ajaxPath+'downloadFile',formData)
                if (res.data.result == 'success'){
                    baseData = res.data.data.file_data
                } else {
                    this.$refs.alert.show(res.data.err_message);
                }
                this.downLoadPdf(baseData,this.splitFileName(name),this.getFileType(name));
            }
        },
        downLoadPdf(baseData, firstFileName, lastFileName) {
            let fileName = firstFileName + '.' + lastFileName;
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
        //获取文件类型
        getFileType(filePath){
            let startIndex = filePath.lastIndexOf('.');
            if (startIndex != -1)
                return filePath.substring(startIndex+1, filePath.length).toLowerCase();
            else return '';
        },
        //获取文件名
        splitFileName(text){
            let pattern = /\.{1}[a-z]{1,}$/;
            if (pattern.exec(text) !== null) {
                return (text.slice(0, pattern.exec(text).index));
            } else {
                return text;
            }
        },
        //pdf 预览共同处理函数
        pdfPreviewCommon(file_path,file_seq_no,file_name,type){
            if (file_path == '') {
                this.download(file_seq_no,file_name)
                return
            }
            const file = {
                name: file_name,
                file_path: file_path,
            }
            this.$refs.filePreviewDialog.show([file], [], type)
        },
    }
}
</script>

<style scoped>

</style>

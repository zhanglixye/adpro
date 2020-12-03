<template>
    <div class="hello">
        <div class="upload">
            <!--上传部分-->
            <div class="upload_warp" v-if="!accomplish">
                <div class="upload_warp_left" @click="fileClick">
                    <img src="data:image/svg+xml;base64,PHN2ZyBpZD0i5Zu+5bGCXzEiIGRhdGEtbmFtZT0i5Zu+5bGCIDEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgdmlld0JveD0iMCAwIDEwMCA4OC44OSI+PGRlZnM+PHN0eWxlPi5jbHMtMXtmaWxsOiNjY2M7fTwvc3R5bGU+PC9kZWZzPjx0aXRsZT5hZDwvdGl0bGU+PHBhdGggY2xhc3M9ImNscy0xIiBkPSJNODcuNTMsMTIuNzVINDQuMjhMNDEuNyw3LjI1QTEyLjQ5LDEyLjQ5LDAsMCwwLDMwLjQ1LjA2SDEyLjUzQTEyLjYsMTIuNiwwLDAsMCwwLDEyLjc1djYzLjVhMTIuNiwxMi42LDAsMCwwLDEyLjUsMTIuNjloNzVBMTIuNiwxMi42LDAsMCwwLDEwMCw3Ni4yNVYyNS40NUExMi42MSwxMi42MSwwLDAsMCw4Ny41MywxMi43NVpNOTEuNywyNy4zM1Y3Ni4yNWE0LjIxLDQuMjEsMCwwLDEtNC4xNyw0LjIzaC03NWE0LjIxLDQuMjEsMCwwLDEtNC4xNy00LjIzVjEyLjc1YTQuMjEsNC4yMSwwLDAsMSw0LjE3LTQuMjNIMzAuNDVhNC4xOSw0LjE5LDAsMCwxLDMuNzksMi40MUwzOCwxOC44MWE0LjE1LDQuMTUsMCwwLDAsMy43NSwyLjQxSDg3LjUzYTQuMiw0LjIsMCwwLDEsNC4xNyw0LjIzWiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTAuMDMgLTAuMDYpIi8+PHBhdGggY2xhc3M9ImNscy0xIiBkPSJNMzYuNzksNDcuNzlIMzNWNDRhMS41MywxLjUzLDAsMCwwLTEuNDEtMS41MWgtLjFBMS41LDEuNSwwLDAsMCwzMCw0NHYzLjc1SDI2LjNjLTEuMzcuMTItMS41MSwxLjA2LTEuNTEsMS40NmExLjQzLDEuNDMsMCwwLDAsMS41NiwxLjU2SDMwdjMuNjZBMS40NCwxLjQ0LDAsMCwwLDMxLjU3LDU2Yy40LDAsMS4zNC0uMTUsMS40Ni0xLjU2VjUwLjgyaDMuNzZhMS40OSwxLjQ5LDAsMCwwLDEuNTYtMS41MVY0OS4yQTEuNTUsMS41NSwwLDAsMCwzNi43OSw0Ny43OVoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKC0wLjAzIC0wLjA2KSIvPjxwYXRoIGNsYXNzPSJjbHMtMSIgZD0iTTU1LDU2LjUzLDQ5LjQ4LDQyYTIuMiwyLjIsMCwwLDAtMi4wNy0xLjcxLDIuMzEsMi4zMSwwLDAsMC0yLDEuNjhMNDAsNTYuNTFhMy40LDMuNCwwLDAsMC0uMzMsMS4yNmMwLC4zLjExLDEuMjcsMS40NywxLjI3YTEuNDMsMS40MywwLDAsMCwxLjUzLTFsMS0yLjg2aDcuNTFMNTIuMTksNThhMS40MywxLjQzLDAsMCwwLDEuNTQsMSwxLjM3LDEuMzcsMCwwLDAsMS41Ni0xLjIxdi0uMDlsMC0uMDlDNTUuMjIsNTcuMzksNTUuMTIsNTcsNTUsNTYuNTNabS00Ljc1LTQuMTlINDQuNzhsMi43Mi03LjdaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgtMC4wMyAtMC4wNikiLz48cGF0aCBjbGFzcz0iY2xzLTEiIGQ9Ik02Myw0MC40NWgtNC4zQTEuNTcsMS41NywwLDAsMCw1Nyw0Mi4xMVY1Ny4zOEExLjUxLDEuNTEsMCwwLDAsNTguNjksNTloNC42N2M1LS4yNyw3LjY0LTMuNDIsNy45MS05LjQyQzcwLjk0LDQzLjgxLDY4LjE0LDQwLjczLDYzLDQwLjQ1Wm01LDkuMThjLS4xOSw0LjM3LTEuODYsNi41Mi01LjExLDYuNThINjAuMjZWNDMuMjloMi4zOEM2Niw0My40MSw2Ny43Nyw0NS41LDY4LDQ5LjYzWiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTAuMDMgLTAuMDYpIi8+PC9zdmc+" alt="">
                </div>
                <div class="upload_warp_right" @drop="drop($event)" @dragenter="dragenter($event)" @dragover="dragover($event)">
                    またはここにファイルをドロップ
                </div>
            </div>
            <input @change="fileChange($event)" type="file" id="upload_file" accept=".jpg,.jpeg,.png,.gif" multiple style="display: none"/>
            <!--统计部份-->
            <div class="upload_warp_text">
                <span>{{itemList.length}}</span>
                &nbsp;件選択済，{{bytesToSize(this.size)}}
            </div>
            <!--展示部分-->
            <div class="upload_warp_img" v-show="itemList.length !== 0">
                <div class="upload_warp_img_div" v-for="(item,index) of itemList" :key="index">
                    <div class="upload_warp_img_div_top">
                        <div class="upload_warp_img_div_text" :title="item.file_name">
                            {{item.file_name}}
                        </div>
                        <img v-if="!accomplish" src="/images/biz/b00007/del.png" class="upload_warp_img_div_del" @click="thumbnail_del(index)" alt="">
                    </div>
                    <img :src="item.ADThumbnail" alt="">
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'LineFileUploading',
    props:{
        initData: { type: Object, require: true },
        loadingDisplay: { type: Function, require: true },
        axiosFormData:{ type: Function, require: true },
        accomplish: {type: Boolean, default: false},
    },
    data () {
        return {
            imgList: [],
            size: 0,
            itemList:[],
            restrict_file_type:['jpeg','png','gif'],
            upload_state:false
        }
    },
    methods:{
        //this.$parent.$parent.$parent.$refs.alert.show('一個しかアップロードできない。')
        //点击上传区域时
        fileClick() {
            document.getElementById('upload_file').click()
        },
        //input文件变化时
        fileChange(el) {
            if (!el.target.files[0].size) return;
            this.fileList(el.target);
            el.target.value = ''
        },
        //进入拖拽区域
        dragenter(el) {
            el.stopPropagation();   //阻止事件分派到其他节点
            el.preventDefault();    //取消事件的默认动作
        },
        //悬停在拖拽区域上
        dragover(el) {
            el.stopPropagation();   //阻止事件分派到其他节点
            el.preventDefault();    //取消事件的默认动作
        },
        //在拖拽区域中释放
        drop(el) {
            el.stopPropagation();   //阻止事件分派到其他节点
            el.preventDefault();    //取消事件的默认动作
            this.fileList(el.dataTransfer);
        },
        //判断上传的文件是否是多个，如果是多个文件就给出提示，否则进行处理
        fileList(fileList) {
            let files = fileList.files;
            for (let i = 0; i < files.length; i++) {
                //判断是否为文件夹
                if (files[i].type !== '') {
                    this.fileAdd(files[i]);
                } else {
                    //文件夹处理
                    this.folders(fileList.items[i]);
                }
            }
        },
        //文件夹处理
        folders(files) {
            let _this = this;
            //判断是否为原生file
            if (files.kind) {
                files = files.webkitGetAsEntry();
            }
            files.createReader().readEntries(function (file) {
                for (let i = 0; i < file.length; i++) {
                    if (file[i].isFile) {
                        _this.foldersAdd(file[i]);
                    } else {
                        _this.folders(file[i]);
                    }
                }
            })
        },
        //文件夹处理函数内调用的【文件上传】函数
        foldersAdd(entry) {
            let _this = this;
            entry.file(function (file) {
                _this.fileAdd(file)
            })
        },
        //添加文件
        fileAdd(file) {
            this.restrictFilesType(file);
            if (this.upload_state === true){
                //总大小
                this.size = this.size + file.size;
                //判断是否为图片文件
                var _this = this;
                if (file.type.indexOf('image') === -1) {
                    let reader = new FileReader();
                    reader.vue = this;
                    reader.readAsDataURL(file);
                    reader.onload = function () {
                        file.src = this.result;
                        file.thumbnail = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMgAAADICAMAAACahl6sAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAABUUExURQAAAIqKioqKioqKiouLi4qKio+Pj4uLi4qKiomJiYmJiYqKioeHh4mJiYmJiYmJiYqKiomJiYmJiYmJiYqKiomJiYqKioqKioqKioqKiouLi4qKiq75E2wAAAAbdFJOUwCfYM9/vxBAwICgMCDwcNDvUN+vsF+QP+BvT3TrUR4AAALFSURBVHja7d3dcpswEIZh2a4l8Wsc201S3f99ttOTYkhCRmhXgr7fMQM8I0ALaCRjCCGEEEIIIf/SvJ9PQSgHPcbjJUhGS+JkGWqS9yAfDckhhF1IdBzykmMIu5C0IexDUod9SFwI+5D8nB/MJ0qnKpn2hPU12a69Zps0z0e5uIT79ppX1/Oz926NPERGUj21R9p9e807fgzprBJEQjKG9EYMUotLRpDOyEGMuGQEqSUh4pIR5CgKkZaMIE4WIixRhMhKNCGiElXI7B3usFWIoEQZIifRhohJ1CFSEn2IkCQDZCa52Y1CZpKT3ShEQpIHIiDJBEkvyQVJLskGSS3JB0ksWYK0PxbSRkPSSpYgi9+43bcgVlyiBHFGWqIE+eyTWTqJEuRkpCVKkM83SyXRgrxYYYkkpP7eW0caiSSkCooSScjkBE+i94kkxM5+i/yyYpIliHUL+eqQ97l7OH+YYbJZVVTRuGJwSGGQL36+bQzi9gKZPIE3DIkdslMexN53Avlw1M42Ica97QTypz+57ARiTNt7/5YVsqbWWtHrAAECBAgQIEBEIM3SuPFmIxDNAAECBAgQIECkIYtDOOLSllc0xsUBAQIECBAgQHJAFodwxMVSNAIBAgQIECBAqLWAAAECBAgQIH/TeJE01FpAgAABAgQIkDUlylkk+iUK1S8QIECAAAGyJwhFIxAgQIAAAQIECBAgQID8h5A+N6RKBPG5IadEkNDmdYzX/FwHqfNC6mSQcM3puIZ0kKHJ52iGdZDnD3BDtja5Divnv5nOyXvIcse308WWI2Yuns08eHu8ql5hzevjNj2HmJ6gDwUmpm+2XXmOuDU/C2ySyJUZL6U5YheTbQq7uLroB6crC7LimekKapNu1WtRW8x9clnbIR+LaJQuwUqytve5Gb5PtGa0dVVV+yypq8pZQwghhBBCCBnlN01YLvZFk4ENAAAAAElFTkSuQmCC';
                        this.vue.imgList.push({
                            file
                        });
                        _this.uploadFiles(file)
                    }
                } else {
                    let reader = new FileReader();
                    reader.vue = this;
                    reader.readAsDataURL(file);
                    reader.onload = function () {
                        file.src = this.result;
                        file.thumbnail = this.result;
                        this.vue.imgList.push({
                            file
                        });
                        _this.uploadFiles(file)
                    }
                }
            } else {
                this.$parent.$parent.$parent.$refs.alert.show('拡張子が「jpeg,png,gif」のファイルのみアップできます。');
            }
        },
        //计算文件大小
        bytesToSize(bytes) {
            if (bytes === 0) return '0 B';
            let k = 1000, // or 1024
                sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
                i = Math.floor(Math.log(bytes) / Math.log(k));
            return (bytes / Math.pow(k, i)).toPrecision(3) + ' ' + sizes[i];
        },
        //文件上传接口
        async uploadFiles(file){
            try {
                this.loadingDisplay(true);
                var _this = this;
                let parameter = this.axiosFormData();
                parameter.append('file_name',file.name);
                parameter.append('file_data',file.src);
                await  axios.post('/api/biz/b00016/s00022/uploadFile',parameter).then((res)=>{
                    if (res.data.result == 'success'){
                        _this.$emit('receiveResultFile',res.data)
                    } else {
                        _this.$parent.$parent.$parent.$refs.alert.show(JSON.stringify(res.data))
                    }
                })
            } catch (err) {
                console.error(err);
            } finally {
                this.loadingDisplay(false)
            }
        },
        //限制文件上传类型
        restrictFilesType(file){
            this.upload_state = null;
            var type = file.type.split('/')[1];
            this.restrict_file_type.indexOf(type) === -1 ? this.upload_state = false : this.upload_state = true;
        },
        //设置缩略图默认显示
        async thumbnailDefaultShow(src){
            try {
                var _this = this;
                await axios.get('/api/utilities/getFileReferenceUrlForThumbnail?file_path='+src).then((res)=>{
                    _this.thumbnail = res.data.url;
                });
            } catch (err) {
                console.error(err);
            }
        },
        //删除缩略图
        thumbnail_del(index) {
            let size = 0;
            this.imgList.splice(index, 1);
            this.itemList.splice(index, 1);
            //设置缩略图大小
            this.itemList.map((item)=>{
                size += item.file_size
            })
            this.size = size;
        }
    }
}
</script>

<style scoped>
    /*清除默认样式*/
    p{margin: 0 !important;}
    .project-title{
        font-weight: bold;
        color: #555555;
        line-height: 50px;
    }
    .upload_warp_img_div_del {
        position: absolute;
        top: 6px;
        width: 16px;
        right: 4px;
    }
    .upload_warp_img_div_top {
        position: absolute;
        top: 0;
        width: 100%;
        height: 30px;
        background-color: rgba(0, 0, 0, 0.4);
        line-height: 30px;
        text-align: left;
        color: #fff;
        font-size: 12px;
        text-indent: 4px;
    }
    .upload_warp_img_div_text {
        white-space: nowrap;
        width: 80%;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .upload_warp_img_div img {
        max-width: 100%;
        max-height: 100%;
        vertical-align: middle;
    }
    .upload_warp_img_div {
        position: relative;
        height: 100px;
        width: 120px;
        border: 1px solid #ccc;
        margin: 0 30px 10px 0;
        float: left;
        line-height: 100px;
        display: table-cell;
        text-align: center;
        background-color: #eee;
        cursor: pointer;
    }
    .upload_warp_img {
        border-top: 1px solid #D2D2D2;
        padding: 14px 0 0 14px;
        overflow: hidden
    }
    .upload_warp_text {
        text-align: left;
        margin-bottom: 10px;
        padding-top: 10px;
        text-indent: 14px;
        border-top: 1px solid #ccc;
        font-size: 14px;
    }
    .upload_warp_right {
        float: left;
        width: 57%;
        margin-left: 2%;
        height: 100%;
        border: 1px dashed #999;
        border-radius: 4px;
        line-height: 130px;
        color: #999;
    }
    .upload_warp_left img {
        display: block;
        transform: translateY(28px);
        margin: 0 auto;
        width: 80px;
    }
    .upload_warp_left {
        float: left;
        width: 40%;
        height: 100%;
        border: 1px dashed #999;
        border-radius: 4px;
        cursor: pointer;
    }
    .upload_warp {
        margin: 14px;
        height: 130px;
    }
    .upload {
        border: 1px solid #ccc;
        background-color: #fff;
        width: 100%;
        box-shadow: 0 1px 0 #ccc;
        border-radius: 4px;
    }
    .hello {
        width: 100%;
        text-align: center;
    }
</style>

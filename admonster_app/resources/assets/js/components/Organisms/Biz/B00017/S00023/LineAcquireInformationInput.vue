<template>
    <div class="px-3" style="height: 100%">
        <div class="top-link-box">
            <button
                @click="switchModule"
            >
                <svg width="19px" height="12px"><g transform="matrix(1 0 0 1 -40 -17 )"><path d="M 19 1  L 17 1  L 17 5  L 3.83 5  L 7.41 1.41  L 6 0  L 0 6  L 6 12  L 7.41 10.59  L 3.83 7  L 19 7  L 19 1  Z " fill-rule="nonzero" fill="#7f7f7f" stroke="none" transform="matrix(1 0 0 1 40 17 )" /></g></svg>
            </button>
            <a @click="showPdfLink">マニュアルを参照する</a>
        </div>
        <div class="top-title">タイムライン</div>
        <div class="info-box">
            <h1 class="item-title">ADキャプチャー：</h1>
            <line-file-uploading
                ref="lineFileUploading"
                :initData="initData"
                :accomplish="accomplish"
                :loadingDisplay="loadingDisplay"
                :axiosFormData="axiosFormData"
                @receiveResultFile="resultFile"
            />
            <v-checkbox
                class="post-letter"
                v-model="C00400_9"
                label="アカウント名、配信日・時間がキャプチャ内に入っています"
                hide-details
                :disabled="accomplish"
            />
            <v-checkbox
                class="post-letter"
                v-model="C00400_10"
                label="左記のアカウント・上記の取得対象に一致しています"
                hide-details
                :disabled="accomplish"
            />
            <v-checkbox
                class="post-letter"
                v-model="C00400_11"
                label="いいね！等のリアクションまでを取得しています"
                hide-details
                :disabled="accomplish"
            />
            <v-checkbox
                class="post-letter"
                v-model="C00400_12"
                label="（動画・画像）マニュアルを確認しました"
                hide-details
                :disabled="accomplish"
            />
            <h1 class="item-title">LPキャプチャー：</h1>
            <div v-show="!accomplish" class="item-operation-box">
                <span title="素材を追加" @click="addProject">
                <svg class="operationProject" width="22" height="22"><g transform="matrix(1 0 0 1 -35 -134 )"><path d="M 4.4 22  L 4.4 19.8  L 6.6 19.8  L 6.6 22  L 4.4 22  Z M 0 6.6  L 0 4.4  L 2.2 4.4  L 2.2 6.6  L 0 6.6  Z M 0 11  L 0 8.8  L 2.2 8.8  L 2.2 11  L 0 11  Z M 4.4 2.2  L 4.4 0  L 0 0  L 0 2.2  L 4.4 2.2  Z M 15.4 2.2  L 15.4 0  L 17.6 0  L 17.6 2.2  L 15.4 2.2  Z M 11 2.2  L 11 0  L 13.2 0  L 13.2 2.2  L 11 2.2  Z M 0 17.6  L 0 19.8  L 0 22  L 2.2 22  L 2.2 17.6  L 0 17.6  Z M 6.6 2.2  L 6.6 0  L 8.8 0  L 8.8 2.2  L 6.6 2.2  Z M 0 15.4  L 0 13.2  L 2.2 13.2  L 2.2 15.4  L 0 15.4  Z M 19.8 13.2  L 19.8 11  L 22 11  L 22 13.2  L 19.8 13.2  Z M 19.8 17.6  L 19.8 15.4  L 22 15.4  L 22 17.6  L 19.8 17.6  Z M 22 0  L 19.8 0  L 19.8 4.4  L 22 4.4  L 22 0  Z M 19.8 8.8  L 19.8 6.6  L 22 6.6  L 22 8.8  L 19.8 8.8  Z M 8.8 22  L 8.8 19.8  L 11 19.8  L 11 22  L 8.8 22  Z M 22 19.8  L 17.6 19.8  L 17.6 22  L 22 22  L 22 19.8  Z M 13.2 22  L 13.2 19.8  L 15.4 19.8  L 15.4 22  L 13.2 22  Z M 12.1 12.1  L 17.6 12.1  L 17.6 9.9  L 12.1 9.9  L 12.1 4.4  L 9.9 4.4  L 9.9 9.9  L 4.4 9.9  L 4.4 12.1  L 9.9 12.1  L 9.9 17.6  L 12.1 17.6  L 12.1 12.1  Z " fill-rule="nonzero" fill="#7f7f7f" stroke="none" transform="matrix(1 0 0 1 35 134 )" /></g></svg>
            </span>
                <span title="素材を削除" @click="delProject">
                <svg class="operationProject" width="22" height="22"><g transform="matrix(1 0 0 1 -72 -134 )"><path d="M 4.4 22  L 4.4 19.8  L 6.6 19.8  L 6.6 22  L 4.4 22  Z M 0 6.6  L 0 4.4  L 2.2 4.4  L 2.2 6.6  L 0 6.6  Z M 0 11  L 0 8.8  L 2.2 8.8  L 2.2 11  L 0 11  Z M 4.4 2.2  L 4.4 0  L 0 0  L 0 2.2  L 4.4 2.2  Z M 15.4 2.2  L 15.4 0  L 17.6 0  L 17.6 2.2  L 15.4 2.2  Z M 11 2.2  L 11 0  L 13.2 0  L 13.2 2.2  L 11 2.2  Z M 0 17.6  L 0 19.8  L 0 22  L 2.2 22  L 2.2 17.6  L 0 17.6  Z M 6.6 2.2  L 6.6 0  L 8.8 0  L 8.8 2.2  L 6.6 2.2  Z M 0 15.4  L 0 13.2  L 2.2 13.2  L 2.2 15.4  L 0 15.4  Z M 19.8 13.2  L 19.8 11  L 22 11  L 22 13.2  L 19.8 13.2  Z M 19.8 17.6  L 19.8 15.4  L 22 15.4  L 22 17.6  L 19.8 17.6  Z M 22 0  L 19.8 0  L 19.8 4.4  L 22 4.4  L 22 0  Z M 19.8 8.8  L 19.8 6.6  L 22 6.6  L 22 8.8  L 19.8 8.8  Z M 8.8 22  L 8.8 19.8  L 11 19.8  L 11 22  L 8.8 22  Z M 22 19.8  L 17.6 19.8  L 17.6 22  L 22 22  L 22 19.8  Z M 13.2 22  L 13.2 19.8  L 15.4 19.8  L 15.4 22  L 13.2 22  Z M 12.1 12.1  L 17.6 12.1  L 17.6 9.9  L 12.1 9.9  L 9.9 9.9  L 4.4 9.9  L 4.4 12.1  L 9.9 12.1  L 12.1 12.1  Z " fill-rule="nonzero" fill="#7f7f7f" stroke="none" transform="matrix(1 0 0 1 72 134 )" /></g></svg>
            </span>
            </div>
            <table class="item-list-box" cellspacing="0">
                <thead>
                <tr>
                    <th class="check"><input class="checkAll" @click="selectAll" type="checkbox" v-model="checkAllState" :disabled="accomplish"/></th>
                    <th>画像素材</th>
                    <th>AD位置</th>
                </tr>
                </thead>
                <tbody ref="parentNode">
                <tr v-for="(item,index) in G00800_7" :key="item.indexVal" :data-value="index">
                    <td class="check">
                        <input class="lpCheckState" @click="select" type="checkbox" :checked="item.itemCheck" :disabled="accomplish"/>
                    </td>
                    <td>
                        <img
                            v-if="item.file_path == ''"
                            class="icon"
                            @click="fileClick(index)"
                            src="data:image/svg+xml;base64,PHN2ZyBpZD0i5Zu+5bGCXzEiIGRhdGEtbmFtZT0i5Zu+5bGCIDEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgdmlld0JveD0iMCAwIDEwMCA4OC44OSI+PGRlZnM+PHN0eWxlPi5jbHMtMXtmaWxsOiNjY2M7fTwvc3R5bGU+PC9kZWZzPjx0aXRsZT5MUDwvdGl0bGU+PHBhdGggY2xhc3M9ImNscy0xIiBkPSJNODcuNSwxMi43NUg0NC4yNWwtMi41OC01LjVBMTIuNDksMTIuNDksMCwwLDAsMzAuNDIuMDZIMTIuNUExMi42LDEyLjYsMCwwLDAsMCwxMi43NXY2My41QTEyLjYsMTIuNiwwLDAsMCwxMi41LDg4Ljk0aDc1QTEyLjYsMTIuNiwwLDAsMCwxMDAsNzYuMjVWMjUuNDVBMTIuNjEsMTIuNjEsMCwwLDAsODcuNSwxMi43NVptNC4xNywxNC41OFY3Ni4yNWE0LjIxLDQuMjEsMCwwLDEtNC4xNyw0LjIzaC03NWE0LjIxLDQuMjEsMCwwLDEtNC4xNy00LjIzVjEyLjc1QTQuMjEsNC4yMSwwLDAsMSwxMi41LDguNTJIMzAuNDJhNC4xOSw0LjE5LDAsMCwxLDMuNzksMi40MWwzLjcxLDcuODhhNC4xNSw0LjE1LDAsMCwwLDMuNzUsMi40MUg4Ny41YTQuMiw0LjIsMCwwLDEsNC4xNyw0LjIzWiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMCAtMC4wNikiLz48cGF0aCBjbGFzcz0iY2xzLTEiIGQ9Ik0zNi43Niw0Ny43OUgzM1Y0NGExLjUzLDEuNTMsMCwwLDAtMS40MS0xLjUxaC0uMUExLjUsMS41LDAsMCwwLDMwLDQ0djMuNzVoLTMuN2MtMS4zNy4xMi0xLjUxLDEuMDYtMS41MSwxLjQ2YTEuNDMsMS40MywwLDAsMCwxLjU2LDEuNTZIMzB2My42NkExLjQ0LDEuNDQsMCwwLDAsMzEuNTQsNTZjLjQsMCwxLjM0LS4xNSwxLjQ2LTEuNTZWNTAuODJoMy43NmExLjQ5LDEuNDksMCwwLDAsMS41Ni0xLjUxVjQ5LjJBMS41NSwxLjU1LDAsMCwwLDM2Ljc2LDQ3Ljc5WiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMCAtMC4wNikiLz48cGF0aCBjbGFzcz0iY2xzLTEiIGQ9Ik01MS4zOCw1Ni4ySDQ3LjE0VjQyLjA3YTEuNjQsMS42NCwwLDAsMC0xLjYxLTEuNjJBMS41NSwxLjU1LDAsMCwwLDQ0LDQyLjExVjU3LjUzYTEuNTQsMS41NCwwLDAsMCwuMzQsMS4yMywxLjcyLDEuNzIsMCwwLDAsMS4zMS40N2g1LjcxYTEuNDUsMS40NSwwLDAsMCwxLjU2LTEuNDF2LS4xQTEuNTgsMS41OCwwLDAsMCw1MS4zOCw1Ni4yWiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMCAtMC4wNikiLz48cGF0aCBjbGFzcz0iY2xzLTEiIGQ9Ik02My4zMiw0MC41NUg1OS4wN2ExLjcyLDEuNzIsMCwwLDAtMS4zMS40NywxLjUzLDEuNTMsMCwwLDAtLjM1LDEuMTlWNTcuNjdBMS40LDEuNCwwLDAsMCw1OSw1OS4xNGExLjQ3LDEuNDcsMCwwLDAsMS41Ny0xLjQ3di01LjFoMi45MmMzLjc0LS4yNyw1LjgxLTIuMjgsNi4xNi02QzY5LjM0LDQyLjg0LDY3LjIzLDQwLjgzLDYzLjMyLDQwLjU1Wm0zLjA4LDZjLS4xNywyLTEuMTYsMy0zLjA4LDMuMTFINjAuNTRWNDMuMzhINjMuMkEzLDMsMCwwLDEsNjYuNCw0Ni41MloiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAgLTAuMDYpIi8+PC9zdmc+"
                            >
                        <div v-else class="material-box">
                            <div class="img-box">
                                <div class="material-info">
                                    <span :title="item.file_name">{{item.file_name}}</span>
                                    <img
                                        v-show="!accomplish"
                                        @click="delMateria(item)"
                                        src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgBAMAAACBVGfHAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAAAeUExURQAAAP///////////////////////////////////yR8m5UAAAAJdFJOUwAQ0M/Av7Awr7TXxSMAAACXSURBVCjPrdG7DYQwEARQ3MEJV0BISHgVnFzB6UogPxrhJ6Zb8MwiL7mdrLV6tmbtpqmypu2lTfh9WRMGNSI2VmAhCW+AjREiEdjZaEUy6HRXT3KB2W4nKcCIAyIOiHhA4oGIB8yw+EbMR4YneJCcoXeEIVtHGDIUYlMUYlMUMlrIixz2hAqZiT1yd0+0sv73+xvSp87HnpAGR+BN/N7DAAAAAElFTkSuQmCC"
                                    >
                                </div>
                                <img
                                    v-if="item.thumbnail != ''"
                                    :src="item.thumbnail"
                                >
                                <img
                                    v-else
                                    :src="item.materialData"
                                >
                            </div>
                        </div>
                    </td>
                    <td>
                        <textarea class="pa-2" v-model="item.adPosition" :disabled="accomplish"></textarea>
                    </td>
                </tr>
                <input @change="fileChange($event)" type="file" id="lp_upload_file" accept=".jpg,.jpeg,.png,.gif" style="display: none"/>
                </tbody>
            </table>
            <v-checkbox
                class="post-letter"
                v-model="C00400_13"
                label="リンク先のみ取得しました(画像や動画は取得していません)"
                hide-details
                :disabled="accomplish"
            />
            <v-checkbox
                class="post-letter"
                v-model="C00400_14"
                label="ファーストビューのみ取得しました"
                hide-details
                :disabled="accomplish"
            />
            <v-checkbox
                class="post-letter"
                v-model="C00400_15"
                label="権限許可の画面ではありません"
                hide-details
                :disabled="accomplish"
            />
            <v-checkbox
                class="post-letter"
                v-model="C00400_16"
                label="ロードが終わってから取得しました"
                hide-details
                :disabled="accomplish"
            />
            <v-checkbox
                class="post-letter"
                v-model="C00400_17"
                label="再生・ダウンロードバーなどが映り込んでいません"
                hide-details
                :disabled="accomplish"
            />
            <h1 class="item-title">備考：</h1>
            <textarea class="remarks px-3 py-2 mb-4" v-model="C00200_8" :disabled="accomplish"></textarea>
            <div v-show="!accomplish" style="margin: 0 0 20px 0;text-align: center;">
                <v-btn
                    color="warning"
                    style="width: 100px;margin: 0;"
                    @click="switchModule"
                >確定</v-btn>
            </div>
        </div>
        <file-preview-dialog
            ref="file_Preview_Dialog"
            :isWide=true
        />
    </div>
</template>

<script>
import LineFileUploading from './LineFileUploading'
import FilePreviewDialog from '../../../../Atoms/Dialogs/FilePreviewDialog';
export default {
    name: 'LineAcquireInformationInput',
    components: {
        LineFileUploading,
        FilePreviewDialog
    },
    props: {
        initData: {type: Object, require: true},
        edit: {type: Boolean, require: false, default: false},
        loadingDisplay: {type: Function, require: true},
        axiosFormData: {type: Function, require: true},
        getThumbnail: {type: Function, require: true},
        accomplish: {type: Boolean, default: false},
        projectInfo: {type: Object , require: true}
    },
    data: () => ({
        //按从上倒下的顺序的checkbox绑定的值
        C00400_9: false,
        C00400_10: false,
        C00400_11: false,
        C00400_12: false,
        C00400_13: false,
        C00400_14: false,
        C00400_15: false,
        C00400_16: false,
        C00400_17: false,
        C00800_6: [],  //AD素材的数据
        G00800_7: [], //LP的数据
        C00200_8: '',    //備考
        checkAllState: false,
        LPValue: null,  //当前所在LP的位置
        restrict_file_type: ['jpeg', 'png', 'gif'], //LP文件格式上传的限制
    }),
    computed: {
        workState: function (){
            return JSON.parse(this.initData.task_result_info.content).results.type
        }
    },
    methods: {
        //点击返回按钮范回到 List 组件
        switchModule:function (){
            this.$parent.$parent.businessState = false;
            //找到 lineTable 组件 并替换其中的值
            let data = this.$parent.$parent.$refs.lineList.$refs.lineTable.G00000_3[this.projectInfo.indexVal];
            data.C00400_9 = this.C00400_9;
            data.C00400_10 = this.C00400_10;
            data.C00400_11 = this.C00400_11;
            data.C00400_12 = this.C00400_12;
            data.C00400_13 = this.C00400_13;
            data.C00400_14 = this.C00400_14;
            data.C00400_15 = this.C00400_15;
            data.C00400_16 = this.C00400_16;
            data.C00400_17 = this.C00400_17;
            data.C00800_6 = this.C00800_6;
            data.G00800_7 = this.G00800_7;
            data.C00200_8 = this.C00200_8;
            this.restoreDefaultData();
        },
        //还原组件默认数据
        restoreDefaultData:function (){
            this.C00400_9 = false;
            this.C00400_10 = false;
            this.C00400_11 = false;
            this.C00400_12 = false;
            this.C00400_13 = false;
            this.C00400_14 = false;
            this.C00400_15 = false;
            this.C00400_16 = false;
            this.C00400_17 = false;
            this.C00800_6 = [];
            this.G00800_7 = [];
            this.C00200_8 = '';
            this.checkAllState = false;
            this.$refs.lineFileUploading.imgList = [];
            this.$refs.lineFileUploading.size = 0;
            this.$refs.lineFileUploading.itemList = [];
            this.$refs.lineFileUploading.upload_state = false;
        },
        //点击【全选按钮】进行全选与取消全选
        selectAll: function () {
            this.checkAllState = !this.checkAllState;
            if (this.checkAllState === true) {
                for (let i = 0; i < this.G00800_7.length; i++) {
                    this.G00800_7[i].itemCheck = true;
                }
            } else {
                for (let j = 0; j < this.G00800_7.length; j++) {
                    this.G00800_7[j].itemCheck = false;
                }
            }
        },
        //点击【单选】如果【所有单选选中为真】则【全选状态为选中】否则【反之】
        select: function () {
            var check = document.getElementsByClassName('lpCheckState');//获取到的是个数组
            var checkArr = [];
            this.G00800_7.forEach((item, index) => {
                check[index].checked !== false ? item.itemCheck = true : item.itemCheck = false;
                checkArr.push(item.itemCheck)
            });
            checkArr.indexOf(false) === -1 ? this.checkAllState = true : this.checkAllState = false;
        },
        //点击【添加按钮】新增一个项目
        addProject:function () {
            this.G00800_7.push(
                {
                    itemCheck: false,
                    file_path: '',
                    materialData: '',   //项目回显时所显示的缩略图
                    thumbnail: '',  //本地上传时所显示的缩略图
                    file_name: '',
                    adPosition: '',
                    seq_no: null
                }
            );
            this.$nextTick(function(){
                this.$refs.parentNode.scrollTop = this.$refs.parentNode.scrollHeight;
            });
        },
        //点击【删除按钮】，删除指定的项目
        delProject:function () {
            for (let i = this.G00800_7.length-1;i>-1;i--){
                if (this.G00800_7[i].itemCheck === true){
                    this.G00800_7.splice(i,1);
                }
            }
            if (this.checkAllState === true){
                this.checkAllState = false
            }
        },
        //删除指定LP图像的缩略图
        delMateria:function (item){
            item.file_name = '';
            item.file_path = '';
            item.materialData = '';
            item.thumbnail = '';
            item.file_size = null;
            item.seq_no = null;
        },
        //初始化组件数据
        setFormMsg:function(){
            this.$nextTick(function(){
                let size = 0;
                this.C00400_9 = this.projectInfo.C00400_9;
                this.C00400_10 = this.projectInfo.C00400_10;
                this.C00400_11 = this.projectInfo.C00400_11;
                this.C00400_12 = this.projectInfo.C00400_12;
                this.C00400_13 = this.projectInfo.C00400_13;
                this.C00400_14 = this.projectInfo.C00400_14;
                this.C00400_15 = this.projectInfo.C00400_15;
                this.C00400_16 = this.projectInfo.C00400_16;
                this.C00400_17 = this.projectInfo.C00400_17;
                this.C00800_6 = this.projectInfo.C00800_6;
                if (this.projectInfo.G00800_7.length === 0){
                    if (this.workState == -1){
                        this.G00800_7.push({
                            itemCheck: false,
                            file_path: '',
                            materialData: '',   //项目回显时所显示的缩略图
                            thumbnail: '',  //本地上传时所显示的缩略图
                            file_name: '',
                            adPosition: '',
                            seq_no: null
                        })
                    }
                } else {
                    this.G00800_7 = this.projectInfo.G00800_7;
                }
                this.C00200_8 = this.projectInfo.C00200_8;
                //设置缩略图大小
                this.projectInfo.C00800_6.map((item)=>{
                    size += item.file_size
                })
                this.$refs.lineFileUploading.size = size;
                this.$refs.lineFileUploading.itemList = this.projectInfo.C00800_6;
            });
        },
        //获取文件上传组件的文件数据
        async resultFile(msg) {
            let obj = {};
            obj.seq_no = msg.data.seq_no;
            obj.file_name = msg.data.file_name;
            obj.file_path = msg.data.file_path;
            obj.ADThumbnail = '';
            obj.file_size = msg.data.file_size;
            obj.display_size = msg.data.display_size;
            obj.check_file_name = msg.data.check_file_name;
            this.C00800_6.push(obj)
            //设置AD素材缩略图
            this.ADThumbnailShow();
        },
        //点击上传区域时
        fileClick(index) {
            if (!this.accomplish){
                if (this.G00800_7[index].file_path != ''){
                    this.$parent.$parent.$refs.alert.show('一個しかアップロードできない。')
                } else {
                    document.getElementById('lp_upload_file').click()
                    this.LPValue = index;
                }
            }
        },
        //input文件变化时
        fileChange(el) {
            if (!el.target.files[0].size) return;
            this.fileList(el.target,this.LPValue);
            el.target.value = ''
            this.LPValue = null;
        },
        //判断上传的文件是否是多个，如果是多个文件就给出提示，否则进行处理
        fileList(fileList,index) {
            let files = fileList.files;
            if (files.length > 1){
                this.$parent.$parent.$refs.alert.show('一個しかアップロードできない。')
            } else {
                this.fileAdd(files[0],index);
            }
        },
        //限制文件上传类型
        restrictFilesType(file){
            var type = file.type.split('/')[1];
            return this.restrict_file_type.indexOf(type) === -1 ? false : true;
        },
        //添加文件
        fileAdd(file,index) {
            if (this.restrictFilesType(file)){
                //判断是否为图片文件
                var _this = this;
                let reader = new FileReader();
                reader.vue = this;
                reader.readAsDataURL(file);
                reader.onload = function () {
                    file.src = this.result;
                    file.thumbnail = this.result;
                    _this.G00800_7[index].thumbnail = this.result;
                    _this.uploadFiles(file,index);
                }
            } else {
                this.$parent.$parent.$refs.alert.show('拡張子が「jpeg,png,gif」のファイルのみアップできます。');
            }
        },
        //文件上传接口
        async uploadFiles(file,index){
            try {
                this.loadingDisplay(true);
                var _this = this;
                let parameter = this.axiosFormData();
                parameter.append('file_name',file.name);
                parameter.append('file_data',file.src);
                await axios.post('/api/biz/b00016/s00022/uploadFile',parameter).then((res)=>{
                    if (res.data.result == 'success'){
                        //文件上传成功后，利用返回的路径作为参数，发送获取缩略图的请求，并赋值到LPList所对应的对象上
                        _this.G00800_7[index].seq_no = res.data.data.seq_no;
                        _this.G00800_7[index].file_name = res.data.data.file_name;
                        _this.G00800_7[index].file_path = res.data.data.file_path;
                    } else {
                        _this.$parent.$parent.$refs.alert.show(JSON.stringify(res.data))
                    }
                })
            } catch (err) {
                console.error(err);
            } finally {
                this.loadingDisplay(false)
            }
        },
        //LP的图像回显方法，请求缩略图接口
        LPThumbnailShow(){
            this.$nextTick(async function(){
                const _this = this;
                for (let i = 0; i < this.G00800_7.length; i++) {
                    if (this.G00800_7[i].thumbnail == '') {
                        try {
                            this.G00800_7[i].materialData = '/images/biz/b00007/load.gif';
                            let url = this.G00800_7[i].file_path;
                            if (url != '') {
                                //发送第一次缩略图请求
                                let Result = await this.getThumbnail(url);
                                //判断第一次的返回值，200=成功，false=发送请求出现问题，其他=执行循环
                                if (Result.data.status == 200) {
                                    this.G00800_7[i].materialData = Result.data.url;
                                } else if (Result.data.status === false) {
                                    console.error(Result.data.message)
                                    this.G00800_7[i].materialData = null;
                                } else {
                                    //声明自调用函数，没间隔2秒，向后端发送请求，当结果值为成功或者总请求数超过5次，就停止发送请求
                                    (function autoIncrement(count) {
                                        //设置延迟函数，延迟2秒
                                        setTimeout(async function () {
                                            if (count < 4) {
                                                await _this.getThumbnail(url).then((res) => {
                                                    if (res.data.status == 200) {
                                                        try {
                                                            _this.G00800_7[i].materialData = res.data.url;
                                                        } catch (err){
                                                            if (String(err).indexOf('Cannot set property \'materialData\' of undefined') != -1){
                                                                console.error('没有在 LP 的数组中找到 materialData 对象，可能因为在请求期间，您退出了详情页面')
                                                            } else {
                                                                console.error(err)
                                                            }
                                                        }
                                                    } else if (Result.data.status === false) {
                                                        try {
                                                            console.error(Result.data.message)
                                                            _this.G00800_7[i].materialData = null;
                                                        } catch (err){
                                                            if (String(err).indexOf('Cannot set property \'materialData\' of undefined') != -1){
                                                                console.error('没有在 LP 的数组中找到 materialData 对象，可能因为在请求期间，您退出了详情页面')
                                                            } else {
                                                                console.error(err)
                                                            }
                                                        }
                                                    } else {
                                                        count++;
                                                        autoIncrement(count);   //接收到404后，执行自调用，再次发送请求
                                                    }
                                                })
                                            } else {
                                                try {
                                                    console.error('缩略图请求超时，赋值为空');
                                                    _this.G00800_7[i].materialData = null;
                                                } catch (err){
                                                    if (String(err).indexOf('Cannot set property \'materialData\' of undefined') != -1){
                                                        console.error('没有在 LP 的数组中找到 materialData 对象，可能因为在请求期间，您退出了详情页面')
                                                    } else {
                                                        console.error(err)
                                                    }
                                                }
                                            }
                                        }, 2000);
                                    })(0)
                                }
                            } else {
                                console.warn('因为 G00800_7 中第 '+(i+1)+' 个对象中的 file_path 为空，所以没有发送请求');
                            }
                        } catch (err) {
                            console.error(err);
                        }
                    }
                }
            })
        },
        //设置AD素材缩略图的回显
        ADThumbnailShow() {
            this.$nextTick(()=>{
                try {
                    this.C00800_6.forEach(async (item)=>{
                        let _this = this;
                        let url = item.file_path;
                        if (url == null || url == '') {
                            item.ADThumbnail = '';
                        } else {
                            if (item.ADThumbnail == '/images/biz/b00007/load.gif' || item.ADThumbnail == '' || item.ADThumbnail == null) {
                                item.ADThumbnail = '/images/biz/b00007/load.gif';
                                //发送第一次缩略图请求
                                let Result = await this.getThumbnail(url);
                                //判断第一次的返回值，200=成功，false=发送请求出现问题，其他=执行循环
                                if (Result.data.status == 200) {
                                    item.ADThumbnail = Result.data.url;
                                } else if (Result.data.status === false){
                                    console.error(Result.data.message)
                                    item.ADThumbnail = '';
                                } else {
                                    //声明自调用函数，没间隔2秒，向后端发送请求，当结果值为成功或者总请求数超过5次，就停止发送请求
                                    (function autoIncrement(count) {
                                        //设置延迟函数，延迟2秒
                                        setTimeout(async function () {
                                            if (count < 4) {
                                                await _this.getThumbnail(url).then((res) => {
                                                    if (res.data.status == 200) {
                                                        item.ADThumbnail = res.data.url;
                                                    } else if (Result.data.status === false){
                                                        console.error(Result.data.message)
                                                        item.ADThumbnail = '';
                                                    } else {
                                                        count++;
                                                        autoIncrement(count);   //接收到404后，执行自调用，再次发送请求
                                                    }
                                                })
                                            } else {
                                                console.error('缩略图请求超时，赋值为空');
                                                item.ADThumbnail = '';
                                            }
                                        }, 2000);
                                    })(0)
                                }
                            }
                        }
                    })
                } catch (err) {
                    console.error(err);
                }
            })
        },
        //マニュアルを参照する链接
        showPdfLink(){
            let type = '';
            const file = {
                name: 'マニュアルを参照する.pdf',
                file_path: 'manuals/B00016/S00022/マニュアルを参照する.pdf',
            }
            this.$refs.file_Preview_Dialog.show([file], [], type);
        }
    }
}
</script>

<style scoped lang="scss">
/***
 * 组件内的样式
 */
.top-link-box {
        a {
            display: block;
            font-size: 12px;
            float: right;
            line-height: 30px;
            margin: 12px 0 8px 0;
        }
        button {
            width: 60px;
            height: 30px;
            margin: 10px 0;
            outline: none;
            display: block;
            float: left;
            background-color: #f2f2f2;
            border-radius: 2px;
            transition: background-color 0.1s;
            svg {
                transform: translateY(2px);
            }
        }
        button:hover {
            background-color: #e5e5e5;
        }
        button:active {
            background-color: #dadada;
        }
    }
.top-link-box::after {
    display: block;
    content: '';
    clear: both;
}
.top-title {
    font-size: 16px;
    color: #ffffff;
    text-align: center;
    line-height: 36px;
    font-weight: bold;
    background-color: #4db6ac;
}
.info-box{
    height: 554px;
    overflow-y: auto;
    .item-title{
        font-size: 16px;
        font-weight: bold;
        color: #555555;
        margin: 15px 0;
    }
    .post-letter{
        margin: 10px 0;
    }
    .post-letter ::v-deep {
        label{
            margin-top: 1px;
        }
    }
    .item-operation-box::after{
        display: block;
        content: '';
        clear: both;
    }
    .item-operation-box{
        margin-bottom: 8px;
        span{
            display: inline-block;
            width: 22px;
            height: 22px;
            cursor: pointer;
            float: left;
            margin-right: 12px;
            transform: scale(0.9,0.9);
            .operationProject:active{
                path {
                    fill: #444444;
                }
            }
        }
    }
    .item-list-box{
        width: 100%;
        .check{
            input{
                width:16px;
                height:16px;
                font-size: 16px;
                line-height: 16px;
                color: #7f7f7f;
                font-weight: bold;
                cursor: pointer;
                text-align: center;
                vertical-align: middle;
                position: relative;
            }
            input:checked::before{
                content: "\2714";
            }
            input:disabled::before{
                background-color: #e5e5e5;
                cursor: auto;
            }
            input::before{
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: #ffffff;
                border: 1px solid #aaaaaa;
            }
            .checkAll{
                width: 18px;
                height: 18px;
                font-size: 18px;
                line-height: 18px;
                transform: translateY(-3px);
            }
        }
        thead{
            display: block;
            background-color:  #fcfcfc;
            border: solid 1px #d7d7d7;
            tr{
                width: 100%;
                display: block;
                th{
                    display: block;
                    float: left;
                    color: #555555;
                    padding-top: 2px;
                    line-height: 22px;
                    text-align: center;
                    border-right: solid 1px #d7d7d7;
                }
                th:nth-child(1){
                    width: 6%;
                }
                th:nth-child(2){
                    width: 47%;
                }
                th:nth-child(3){
                    width: 47%;
                    border-right: none;
                }
            }
            tr::after{
                display: block;
                content: '';
                clear: both;
            }
        }
        thead::after,thead::before{
            display: block;
            content: '';
            height: 6px;
        }
        tbody{
            display: block;
            max-height: 260px;
            overflow-y: auto;
            border-bottom: solid 1px #c8c8c8;
            tr{
                width: 100%;
                display: block;
                border: solid 1px #c8c8c8;
                border-top: none;
                padding: 8px 0;
                position: relative;
                overflow: hidden;
                td{
                    display: block;
                    height: 81px;
                    line-height: 81px;
                    float: left;
                    color: #555555;
                    border-right: dotted 1px #d7d7d7;
                    padding: 0 2%;
                }
                td:nth-child(1){
                    width: 6%;
                    text-align: center;
                    input{
                        transform: translateY(-2px);
                    }
                }
                td:nth-child(2){
                    width: 47%;
                    .icon{
                        display: block;
                        width: 80px;
                        margin: 0 auto;
                        cursor: pointer;
                        transform: translateY(5px);
                    }
                    .material-box{
                        display: flex;
                        height: 100%;
                        justify-content: center; /* 水平居中 */
                        align-items: center;     /* 垂直居中 */
                        overflow: hidden;
                        .img-box{
                            display: inline-block;
                            height: 100%;
                            min-width: 100px;
                            position: relative;
                            border: solid 1px #CCC;
                            background-color: #EEE;
                            .material-info{
                                background-color: rgba(0, 0, 0, 0.4);
                                position: absolute;
                                width: 100%;
                                height: 20px;
                                display: flex;
                                z-index: 1;
                                span{
                                    color: #ffffff;
                                    font-size: 12px;
                                    line-height: 20px;
                                    padding-left: 5px;
                                    overflow: hidden;
                                    text-overflow: ellipsis;
                                    white-space: nowrap;
                                }
                                img{
                                    width: 20px;
                                    height: 20px;
                                    padding: 2px;
                                    background-color: rgba(0,0,0,0.4);
                                    cursor: pointer;
                                }
                            }
                            img{
                                height: 100%;
                                margin: 0 auto;
                                display: block;
                            }
                        }
                    }
                }
                td:nth-child(3){
                    width: 47%;
                    border-right: none;
                    textarea{
                        display: block;
                        width: 100%;
                        min-height: 81px;
                        max-height: 81px;
                        line-height: 20px;
                        outline: none;
                        border: solid 1px #cccccc;
                    }
                    textarea[disabled]{
                        background-color: #fafafa;
                    }
                    textarea::-webkit-scrollbar{
                        display:none
                    }
                }
            }
            tr::after{
                display: block;
                content: '';
                clear: both;
            }
            tr:hover{
                .details{
                    right: 0;
                }
            }
            tr:nth-last-child(1){
                border-bottom: none;
            }
        }
        tbody::-webkit-scrollbar{
            display:none
        }
    }
    .remarks{
        display: block;
        width: 100%;
        outline: none;
        border: solid 1px #cccccc;
        min-height: 110px;
        max-height: 110px;
    }
    .remarks[disabled]{
        background-color: #fafafa;
    }
}
.info-box::-webkit-scrollbar{
    display:none
}
button.warning {
    background-color: #fb8c00!important;
    border-color: #fb8c00!important;
}
</style>

<template>
    <div>
        <div v-show="!accomplish" class="item-operation-box">
            <span title="素材を追加" @click="addProject">
                <svg class="operationProject" width="22" height="22"><g transform="matrix(1 0 0 1 -35 -134 )"><path d="M 4.4 22  L 4.4 19.8  L 6.6 19.8  L 6.6 22  L 4.4 22  Z M 0 6.6  L 0 4.4  L 2.2 4.4  L 2.2 6.6  L 0 6.6  Z M 0 11  L 0 8.8  L 2.2 8.8  L 2.2 11  L 0 11  Z M 4.4 2.2  L 4.4 0  L 0 0  L 0 2.2  L 4.4 2.2  Z M 15.4 2.2  L 15.4 0  L 17.6 0  L 17.6 2.2  L 15.4 2.2  Z M 11 2.2  L 11 0  L 13.2 0  L 13.2 2.2  L 11 2.2  Z M 0 17.6  L 0 19.8  L 0 22  L 2.2 22  L 2.2 17.6  L 0 17.6  Z M 6.6 2.2  L 6.6 0  L 8.8 0  L 8.8 2.2  L 6.6 2.2  Z M 0 15.4  L 0 13.2  L 2.2 13.2  L 2.2 15.4  L 0 15.4  Z M 19.8 13.2  L 19.8 11  L 22 11  L 22 13.2  L 19.8 13.2  Z M 19.8 17.6  L 19.8 15.4  L 22 15.4  L 22 17.6  L 19.8 17.6  Z M 22 0  L 19.8 0  L 19.8 4.4  L 22 4.4  L 22 0  Z M 19.8 8.8  L 19.8 6.6  L 22 6.6  L 22 8.8  L 19.8 8.8  Z M 8.8 22  L 8.8 19.8  L 11 19.8  L 11 22  L 8.8 22  Z M 22 19.8  L 17.6 19.8  L 17.6 22  L 22 22  L 22 19.8  Z M 13.2 22  L 13.2 19.8  L 15.4 19.8  L 15.4 22  L 13.2 22  Z M 12.1 12.1  L 17.6 12.1  L 17.6 9.9  L 12.1 9.9  L 12.1 4.4  L 9.9 4.4  L 9.9 9.9  L 4.4 9.9  L 4.4 12.1  L 9.9 12.1  L 9.9 17.6  L 12.1 17.6  L 12.1 12.1  Z " fill-rule="nonzero" fill="#7f7f7f" stroke="none" transform="matrix(1 0 0 1 35 134 )" /></g></svg>
            </span>
            <span title="素材を削除" @click="dleProject">
                <svg class="operationProject" width="22" height="22"><g transform="matrix(1 0 0 1 -72 -134 )"><path d="M 4.4 22  L 4.4 19.8  L 6.6 19.8  L 6.6 22  L 4.4 22  Z M 0 6.6  L 0 4.4  L 2.2 4.4  L 2.2 6.6  L 0 6.6  Z M 0 11  L 0 8.8  L 2.2 8.8  L 2.2 11  L 0 11  Z M 4.4 2.2  L 4.4 0  L 0 0  L 0 2.2  L 4.4 2.2  Z M 15.4 2.2  L 15.4 0  L 17.6 0  L 17.6 2.2  L 15.4 2.2  Z M 11 2.2  L 11 0  L 13.2 0  L 13.2 2.2  L 11 2.2  Z M 0 17.6  L 0 19.8  L 0 22  L 2.2 22  L 2.2 17.6  L 0 17.6  Z M 6.6 2.2  L 6.6 0  L 8.8 0  L 8.8 2.2  L 6.6 2.2  Z M 0 15.4  L 0 13.2  L 2.2 13.2  L 2.2 15.4  L 0 15.4  Z M 19.8 13.2  L 19.8 11  L 22 11  L 22 13.2  L 19.8 13.2  Z M 19.8 17.6  L 19.8 15.4  L 22 15.4  L 22 17.6  L 19.8 17.6  Z M 22 0  L 19.8 0  L 19.8 4.4  L 22 4.4  L 22 0  Z M 19.8 8.8  L 19.8 6.6  L 22 6.6  L 22 8.8  L 19.8 8.8  Z M 8.8 22  L 8.8 19.8  L 11 19.8  L 11 22  L 8.8 22  Z M 22 19.8  L 17.6 19.8  L 17.6 22  L 22 22  L 22 19.8  Z M 13.2 22  L 13.2 19.8  L 15.4 19.8  L 15.4 22  L 13.2 22  Z M 12.1 12.1  L 17.6 12.1  L 17.6 9.9  L 12.1 9.9  L 9.9 9.9  L 4.4 9.9  L 4.4 12.1  L 9.9 12.1  L 12.1 12.1  Z " fill-rule="nonzero" fill="#7f7f7f" stroke="none" transform="matrix(1 0 0 1 72 134 )" /></g></svg>
            </span>
        </div>
        <table class="item-list-box" cellspacing="0">
            <thead>
                <tr>
                    <th class="check"><input class="checkAll" @click="selectAll" type="checkbox" v-model="checkAllState" :disabled="accomplish"/></th>
                    <th>配信日</th>
                    <th>配信時間</th>
                    <th>AD素材</th>
                    <th>LP数</th>
                    <th>備考</th>
                </tr>
            </thead>
            <tbody ref="parentNode">
                <tr v-for="(item,index) in G00000_3" :key="item.indexVal" :data-value="index">
                    <td class="check">
                        <input class="checkState" @click="select" type="checkbox" :checked="item.itemCheck" :disabled="accomplish"/>
                    </td>
                    <td>
                        <v-menu
                            v-model="item.postLetterDateState"
                            :close-on-content-click="false"
                            lazy
                            transition="scale-transition"
                            offset-y
                            full-width
                            min-width="290px"
                            :disabled="accomplish"
                        >
                            <template>
                                <v-text-field
                                    v-model="item.C00700_4"
                                    class="date"
                                    placeholder="YYYY/MM/DD"
                                    slot="activator"
                                    readonly
                                    hide-details
                                    :disabled="accomplish"
                                ></v-text-field>
                            </template>
                            <v-date-picker
                                v-model="item.C00700_4_picker"
                                @input="item.postLetterDateState = false;item.C00700_4 = convertToLocalTime(item.C00700_4_picker)"
                            ></v-date-picker>
                        </v-menu>
                    </td>
                    <td>
                        <v-menu
                            ref="timeMenu"
                            v-model="item.postLetterTimeState"
                            :close-on-content-click="false"
                            lazy
                            transition="scale-transition"
                            offset-y
                            full-width
                            min-width="290px"
                            :disabled="accomplish"
                        >
                            <template >
                                <v-text-field
                                    v-model="item.C00100_5"
                                    class="time"
                                    placeholder="HH:MM"
                                    slot="activator"
                                    readonly
                                    hide-details
                                    :disabled="accomplish"
                                ></v-text-field>
                            </template>
                            <v-time-picker
                                v-model="item.C00100_5"
                                full-width
                                format="24hr"
                                @change="$refs.timeMenu[index].save(item.C00100_5)"
                            ></v-time-picker>
                        </v-menu>
                    </td>
                    <td>
                        <div class="ad-material-box">
                            <img v-if="item.C00800_6.length!==0" :src="item.C00800_6[0].ADThumbnail" alt="">
                            <p v-else>未登録</p>
                        </div>
                    </td>
                    <td>
                        <p>{{item.G00800_7.length}}</p>
                    </td>
                    <td>
                        <p>
                            {{item.C00200_8}}
                        </p>
                    </td>
                    <td class="details" @click="switchModule(item)">
                        <svg width="10px" height="18px"><g transform="matrix(1 0 0 1 -895 -253 )"><path d="M 9.82817869415807 8.58517034068136  C 9.94272623138603 8.70541082164329  10 8.8436873747495  10 9  C 10 9.1563126252505  9.94272623138603 9.29458917835671  9.82817869415807 9.41482965931864  L 1.8213058419244 17.8196392785571  C 1.70675830469645 17.939879759519  1.57502863688431 18  1.42611683848797 18  C 1.27720504009164 18  1.1454753722795 17.939879759519  1.03092783505155 17.8196392785571  L 0.171821305841924 16.9178356713427  C 0.0572737686139748 16.7975951903808  0 16.6593186372745  0 16.503006012024  C 0 16.3466933867735  0.0572737686139748 16.2084168336673  0.171821305841924 16.0881763527054  L 6.92439862542955 9  L 0.171821305841924 1.91182364729459  C 0.0572737686139748 1.79158316633266  0 1.65330661322645  0 1.49699398797595  C 0 1.34068136272545  0.0572737686139748 1.20240480961924  0.171821305841924 1.08216432865731  L 1.03092783505155 0.180360721442886  C 1.1454753722795 0.0601202404809613  1.27720504009164 0  1.42611683848797 0  C 1.57502863688431 0  1.70675830469645 0.0601202404809613  1.8213058419244 0.180360721442886  L 9.82817869415807 8.58517034068136  Z " fill-rule="nonzero" fill="#ffffff" stroke="none" transform="matrix(1 0 0 1 895 253 )" /></g></svg>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
export default {
    name: 'LineTable',
    props: {
        accomplish: {type: Boolean, default: false},
        getThumbnail: {type: Function, require: true}
    },
    data: () => ({
        checkAllState: false,
        G00000_3: [
            {
                indexVal: 0,
                itemCheck: false,
                postLetterDateState: false,
                C00700_4: null,
                C00700_4_picker: null,
                postLetterTimeState: false,
                C00100_5: null,
                C00200_8:'',
                C00400_9: false,
                C00400_10: false,
                C00400_11: false,
                C00400_12: false,
                C00400_13: false,
                C00400_14: false,
                C00400_15: false,
                C00400_16: false,
                C00400_17: false,
                C00800_6: [],
                G00800_7: []
            }
        ],
    }),
    methods: {
        //点击【全选按钮】进行全选与取消全选
        selectAll: function () {
            this.checkAllState = !this.checkAllState;
            if (this.checkAllState === true) {
                for (let i = 0; i < this.G00000_3.length; i++) {
                    this.G00000_3[i].itemCheck = true;
                }
            } else {
                for (let j = 0; j < this.G00000_3.length; j++) {
                    this.G00000_3[j].itemCheck = false;
                }
            }
        },
        //点击【单选】如果【所有单选选中为真】则【全选状态为选中】否则【反之】
        select: function () {
            var check = document.getElementsByClassName('checkState');//获取到的是个数组
            var checkArr = [];
            this.G00000_3.forEach((item, index) => {
                check[index].checked !== false ? item.itemCheck = true : item.itemCheck = false;
                checkArr.push(item.itemCheck)
            });
            checkArr.indexOf(false) === -1 ? this.checkAllState = true : this.checkAllState = false;
        },
        //点击【添加按钮】新增一个项目
        addProject:function () {
            this.G00000_3.push(
                {
                    indexVal: this.G00000_3.length,
                    itemCheck: false,
                    postLetterDateState: false,
                    C00700_4: null,
                    C00700_4_picker: null,
                    postLetterTimeState: false,
                    C00100_5: null,
                    C00200_8:'',
                    C00400_9: false,
                    C00400_10: false,
                    C00400_11: false,
                    C00400_12: false,
                    C00400_13: false,
                    C00400_14: false,
                    C00400_15: false,
                    C00400_16: false,
                    C00400_17: false,
                    C00800_6: [],
                    G00800_7: []
                }
            );
            this.$nextTick(function(){
                this.$refs.parentNode.scrollTop = this.$refs.parentNode.scrollHeight;
            });
        },
        //点击【删除按钮】，删除指定的项目
        dleProject:function () {
            for (let i = this.G00000_3.length-1;i>-1;i--){
                if (this.G00000_3[i].itemCheck === true){
                    this.G00000_3.splice(i,1);
                }
            }
            if (this.checkAllState === true){
                this.checkAllState = false
            }
        },
        //点击【详细按钮】切换右侧模块
        switchModule:function (msg) {
            //向父级组件传递切换状态，控制【v-if】【v-else】
            this.$parent.$parent.$parent.businessState = true;
            this.$parent.$parent.$parent.projectInfo = msg;
            this.$parent.$parent.$parent.$refs.lineInput.setFormMsg();
            this.$parent.$parent.$parent.$refs.lineInput.LPThumbnailShow();
            //为了防止缩略图在加载时进入详情页，导致没有详情页没有缩略图
            this.$parent.$parent.$parent.$refs.lineInput.ADThumbnailShow();
        },
        //设置列表中AD素材的缩略图
        setADThumbnail:function (){
            this.$nextTick(function (){
                const _this = this;
                this.G00000_3.forEach(async (item)=>{
                    let url = item.C00800_6[0].file_path;
                    if (url != null && url != ''){
                        item.C00800_6[0].ADThumbnail = '/images/biz/b00007/load.gif';
                        //发送第一次缩略图请求
                        let Result = await this.getThumbnail(url);
                        //判断第一次的返回值，200=成功，false=发送请求出现问题，其他=执行循环
                        if (Result.data.status == 200) {
                            item.C00800_6[0].ADThumbnail = Result.data.url;
                        } else if (Result.data.status === false){
                            console.error(Result.data.message)
                            item.C00800_6[0].ADThumbnail = '';
                        } else {
                            //声明自调用函数，没间隔2秒，向后端发送请求，当结果值为成功或者总请求数超过5次，就停止发送请求
                            (function autoIncrement(count) {
                                //设置延迟函数，延迟2秒
                                setTimeout(async function () {
                                    if (count < 4) {
                                        await _this.getThumbnail(url).then((res) => {
                                            if (res.data.status == 200) {
                                                item.C00800_6[0].ADThumbnail = res.data.url;
                                            } else if (Result.data.status === false){
                                                console.error(Result.data.message)
                                                item.C00800_6[0].ADThumbnail = '';
                                            } else {
                                                count++;
                                                autoIncrement(count);   //接收到404后，执行自调用，再次发送请求
                                            }
                                        })
                                    } else {
                                        console.error('缩略图请求超时，赋值为空');
                                        item.C00800_6[0].ADThumbnail = '';
                                    }
                                }, 2000);
                            })(0)
                        }
                    }
                });
            })
        },
        //【前台】传给后台的时间转换方法
        convertToLocalTime: function (utcDate, outPutFormat='YYYY/MM/DD') {
            if (utcDate == '' || utcDate == undefined){
                return '';
            }
            return moment.utc(utcDate).local().format(outPutFormat)
        },
    }
}
</script>

<style scoped lang="scss">
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
                width: 22%;
            }
            th:nth-child(3){
                width: 18%;
            }
            th:nth-child(4){
                width: 20%;
            }
            th:nth-child(5){
                width: 12%;
            }
            th:nth-child(6){
                width: 22%;
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
        max-height: 360px;
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
                .date,.time{
                    ::v-deep input{
                        text-align: center;
                    }
                }
            }
            td:nth-child(1){
                width: 6%;
                text-align: center;
                input{
                    transform: translateY(-2px);
                }
            }
            td:nth-child(2){
                width: 22%;
                .v-menu{
                    margin-top: 14px;
                }
            }
            td:nth-child(3){
                width: 18%;
                .v-menu{
                    margin-top: 14px;
                }
            }
            td:nth-child(4){
                width: 20%;
                .ad-material-box{
                    display: flex;
                    height: 100%;
                    justify-content: center; /* 水平居中 */
                    align-items: center;     /* 垂直居中 */
                    overflow: hidden;
                    img{
                        height: 100%;
                    }
                    p{
                        margin: 0;
                        text-align: center;
                    }
                }
            }
            td:nth-child(5){
                width: 12%;
                p{
                    margin: 0;
                    height: 100%;
                    text-align: center;
                }
            }
            td:nth-child(6){
                width: 22%;
                border-right: none;
                position: relative;
                p{
                    max-height: 100%;
                    margin: 0;
                    position: relative;
                    top: 50%;
                    transform: translateY(-50%);
                    line-height: 1.5em;
                    display: -webkit-box;
                    -webkit-box-orient: vertical;
                    -webkit-line-clamp: 4;
                    overflow: hidden;
                }
            }
            .details{
                width: 30px;
                height: 98px;
                padding: 0;
                display: flex;
                cursor: pointer;
                align-items: center;
                justify-content: center;
                background-color: #00D9AD;
                border-right: none;
                position: absolute;
                top: 0;
                right: -31px;
                transition-duration:0.3s;
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
</style>

<template>
    <div>
        <v-card-text class="pa-0">
            <span style="line-height: 32px" v-if="!disabled_state">
                作業時間&nbsp;&nbsp;<a style="text-decoration: underline;font-size: 35px;color:#1976d2;" @click.stop="dialogOpenT">{{savedRealTime.user_timekeeping}}</a>&nbsp;&nbsp;M
            </span>
            <span style="line-height: 32px" v-else>
                作業時間&nbsp;&nbsp;<a style="text-decoration: underline;font-size: 35px; color: #333333;">{{savedRealTime.user_timekeeping}}</a>&nbsp;&nbsp;M
            </span>
        </v-card-text>
        <v-dialog
            v-model="dialog"
            max-width="1000"
            persistent
        >
            <v-card>
                <v-card-title class="pt-3">
                    <v-spacer>
                        <div class="row_overwrite no-gutters">
                            <div class="pa-0 col-6">
                                <div style="font-size: 16px">作業時間登録</div>
                            </div>
                            <div class="pa-0 col-6" style="display: flex;flex-direction: row-reverse">
                                <div style="cursor: pointer">
                                    <v-icon @click="dialog = false">close</v-icon>
                                </div>
                            </div>
                        </div>
                    </v-spacer>
                </v-card-title>
                <v-divider style="margin: 0"></v-divider>
                <div class="pa-5 mt-2" style="padding-top: 0 !important;">
                    <v-spacer>
                        <div class="row_overwrite projection ma-0" style="flex-wrap: nowrap">
                            <div ref="default_time_box" class="mx-4 pa-0 default-time-box col-3">
                                <div class="clock-box" :class="{increase:timekeeping_difference>0,reduce:timekeeping_difference<0}">
                                    <v-progress-circular
                                        :rotate="-90"
                                        :size="210"
                                        :width="5"
                                        :value="default_time.second*1.66667"
                                        color="#4db6ac"
                                    >
                                        <img src="/images/biz/common/time_scale.png" alt="">
                                    </v-progress-circular>
                                    <div class="time-box">
                                        <p class="date">{{default_date}}&nbsp;({{default_week}})</p>
                                        <p class="time">{{default_time.hour}}:{{default_time.minute}}:{{default_time.second}}</p>
                                        <p class="time-zone">サーバー時間<br>（{{default_time_zone}}）</p>
                                    </div>
                                </div>
                                <div
                                    class="timekeeping"
                                    :class="{increase:timekeeping_difference>0,reduce:timekeeping_difference<0}">
                                    <div>
                                        <input
                                            ref="timekeeping"
                                            type="text"
                                            v-model="timekeeping_difference"
                                            :style="{'max-width':timekeeping_width}"
                                            disabled
                                        >
                                        <span>M</span>
                                    </div>
                                    <p>({{(timekeeping_difference/60).toFixed(2)}}/H)</p>
                                </div>
                                <span class="tips">差分 = 手動設定工数 - システム記録工数</span>
                            </div>
                            <div class="ml-6 pa-5 system-time-box col-4">
                                <p class="item-title">システム記録日時</p>
                                <v-text-field
                                    v-model="system_start_date"
                                    label="開始日付"
                                    hide-details
                                    disabled
                                ></v-text-field>
                                <v-text-field
                                    v-model="system_start_time"
                                    label="開始時間"
                                    disabled
                                    hide-details
                                ></v-text-field>
                                <v-text-field
                                    v-model="system_end_date"
                                    label="終了日付"
                                    class="mt-12"
                                    disabled
                                    hide-details
                                ></v-text-field>
                                <v-text-field
                                    v-model="colon_date"
                                    class="text-color"
                                    label="終了時間"
                                    disabled
                                    hide-details
                                ></v-text-field>
                                <v-text-field
                                    v-model="system_timekeeping"
                                    class="text-color ma-zero"
                                    label="作業工数（分）"
                                    disabled
                                    hide-details
                                ></v-text-field>
                                <div>※初回画面起動時からの経過時間で、レポートの「システム工数」とは異なります。</div>
                            </div>
                            <div class="ml-6 pa-5 user-time-box col-4">
                                <p class="item-title">手動設定日時</p>
                                <div class="work-time">
                                    <v-menu
                                        v-model="start_date_switch"
                                        :close-on-content-click="false"
                                        transition="scale-transition"
                                        offset-y
                                        min-width="290px"
                                    >
                                        <template slot="activator">
                                            <v-text-field
                                                v-model="user_start_date"
                                                label="開始日付"
                                                readonly
                                                color="#00BF99"
                                                hide-details
                                            ></v-text-field>
                                        </template>
                                        <v-date-picker v-model="user_start_date" color="#00BF99" @input="start_date_switch = false"></v-date-picker>
                                    </v-menu>
                                    <v-menu
                                        ref="user_start_time"
                                        v-model="start_time_switch"
                                        :close-on-content-click="false"
                                        :return-value.sync="user_start_time"
                                        transition="scale-transition"
                                        offset-y
                                        min-width="290px"
                                    >
                                        <template slot="activator">
                                            <v-text-field
                                                v-model="user_start_time"
                                                label="開始時間"
                                                readonly
                                                color="#00BF99"
                                                hide-details
                                            ></v-text-field>
                                        </template>
                                        <v-time-picker
                                            v-model="user_start_time"
                                            full-width
                                            format="24hr"
                                            color="#00BF99"
                                            @change="$refs.user_start_time.save(user_start_time)"
                                        ></v-time-picker>
                                    </v-menu>
                                    <span @click="getCurrentDate(0)">&lt;現日時を取得する&gt;</span>
                                </div>
                                <p class="item-title mt-2">
                                    <b>システム終了日時を反映</b>
                                    <v-switch
                                        v-model="end_date_disabled"
                                        hide-details
                                    ></v-switch>
                                </p>
                                <div class="work-time" :class="{disabled:end_date_disabled}">
                                    <v-menu
                                    v-model="end_date_switch"
                                    :close-on-content-click="false"
                                    transition="scale-transition"
                                    offset-y
                                    min-width="290px"
                                    :disabled="end_date_disabled"
                                >
                                    <template slot="activator">
                                        <v-text-field
                                            v-model="user_end_date"
                                            label="終了日付"
                                            readonly
                                            color="#00BF99"
                                            :disabled="end_date_disabled"
                                            hide-details
                                        ></v-text-field>
                                    </template>
                                    <v-date-picker v-model="user_end_date" color="#00BF99" @input="end_date_switch = false"></v-date-picker>
                                </v-menu>
                                    <v-menu
                                    ref="user_end_time"
                                    v-model="end_time_switch"
                                    :close-on-content-click="false"
                                    :return-value.sync="user_end_time"
                                    transition="scale-transition"
                                    offset-y
                                    min-width="290px"
                                    :disabled="end_date_disabled"
                                >
                                    <template slot="activator">
                                        <v-text-field
                                            v-model="user_end_time"
                                            label="終了時間"
                                            readonly
                                            color="#00BF99"
                                            :disabled="end_date_disabled"
                                            hide-details
                                        ></v-text-field>
                                    </template>
                                    <v-time-picker
                                        v-model="user_end_time"
                                        full-width
                                        format="24hr"
                                        color="#00BF99"
                                        @change="$refs.user_end_time.save(user_end_time)"
                                    ></v-time-picker>
                                </v-menu>
                                    <span v-show="!end_date_disabled" @click="getCurrentDate(1)">&lt;現日時を取得する&gt;</span>
                                </div>
                                <v-text-field
                                    v-model="user_timekeeping"
                                    class="text-color"
                                    label="作業工数（分）"
                                    color="#00BF99"
                                    :rules="NumberRules"
                                    hide-details
                                ></v-text-field>
                            </div>
                        </div>
                    </v-spacer>
                </div>
                <div class="remark">
                    <textarea v-model="remark" placeholder="備考を入力してください" rows="2"></textarea>
                </div>
                <v-card-actions class="pb-6 pt-6 btn-box" style="">
                    <a class="link" @click="showPdfLinkA">&lt;時間記録説明&gt;</a>
                    <div>
                        <v-btn
                            color="warning"
                            dark
                            style="width: 100px"
                            class="mr-3"
                            @click="clearWorkTime"
                        >
                            リセット
                        </v-btn>
                        <v-btn
                            color="success"
                            style="width: 100px"
                            class="mr-3"
                            @click="saveTime"
                        >
                            保存
                        </v-btn>
                        <v-btn
                            color="#949494"
                            dark
                            style="width: 100px"
                            @click="dialog = false"
                        >
                            戻る
                        </v-btn>
                    </div>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <file-preview-dialog ref="filePreviewDialog" :isWide=true></file-preview-dialog>
        <alert-dialog ref="alert"></alert-dialog>
    </div>
</template>

<script>
import FilePreviewDialog from '../../Dialogs/FilePreviewDialog';
import AlertDialog from '../../Dialogs/AlertDialog';
export default {
    name: 'DateBussT',
    components: {AlertDialog, FilePreviewDialog},
    props: {
        disabled_state: {type: Boolean, default: false}
    },
    data() {
        return {
            dialog: false,  //弹窗开关
            //last: '0',   //入口显示的作业计时
            timekeeping_difference: 0, //作业计时时间
            timekeeping_width: '72px',     //作业计时输入框的宽度
            default_time: {
                hour: '',
                minute: '',
                second: ''
            },  //时钟的时间
            default_date: '',             //时钟的日期
            default_week: '',             //时钟的星期
            default_time_zone: '',        //时钟的时区
            week_day: ['日曜日', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日'],    //星期数组
            system_start_date: '',
            system_start_time: '',
            system_end_date: '',
            system_end_time: '',
            system_timekeeping: '',
            start_date_switch: false,   //开始日期的选择器开关
            start_time_switch: false,   //开始时间的选择器开关
            end_date_switch: false,     //结束日期的选择器开关
            end_time_switch: false,     //结束时间的选择器开关
            end_date_disabled: false,    //システム記録日時を反映
            user_timekeeping: '',
            user_start_date: '',
            user_start_time: '',
            user_end_date: '',
            user_end_time: '',
            remark: '',
            //保持最新的保存数据
            savedRealTime:{
                user_timekeeping: '',
                user_start_date: '',
                user_start_time: '',
                user_end_date: '',
                user_end_time: '',
                end_date_disabled:false,
                remark:''
            },
            //判断是否保存过
            isSaved:false,
            NumberRules:[
                v =>(/^[0-9]\d*$/g.test(v)) || '整数のみ入力できます'
            ]
        }
    },
    computed:{
        colon_date(){
            return this.system_end_time+':'+ this.default_time.second;
        }
    },
    watch: {
        //设置作业计时差值的宽度
        timekeeping_difference: function (){
            this.timekeeping_difference = parseInt(this.timekeeping_difference, 10);
            //如果大于0，拼接字符串 + 号
            if (this.timekeeping_difference > 0){
                this.timekeeping_difference = '+' + this.timekeeping_difference;
            }
            //通过内容长度，计算显示宽度
            if (String(this.timekeeping_difference).length <= 4){
                this.timekeeping_width = '72px';
            } else {
                this.timekeeping_width = String(this.timekeeping_difference).length * 16 + 'px';
            }
        },
        //更新系统记录的工时
        system_end_time: function (){
            this.computeSystemTime();
            if (this.end_date_disabled == true){
                this.user_end_date = this.system_end_date;
                this.user_end_time = this.system_end_time;
            }
        },
        //在 终了指定日時 关闭时，用户终了时间 同步 系统终了时间
        end_date_disabled: function (){
            if (this.end_date_disabled == true){
                this.user_end_date = this.system_end_date;
                this.user_end_time = this.system_end_time;
            }
            //计算工时差异
            this.count_difference();
        },
        user_start_date:function (){
            this.computeTimekeeping()
        },
        user_start_time:function (){
            this.computeTimekeeping()
        },
        user_end_date:function (){
            this.computeTimekeeping()
        },
        user_end_time:function (){
            this.computeTimekeeping()
        },
        user_timekeeping:function (){
            this.count_difference()
        },
        savedRealTime:{
            handler(newValue){
                this.remark = newValue.remark;
            },
            deep: true
        }
    },
    mounted() {
        setInterval(this.updateTime, 1000); //每秒执行一次,时钟时间更新方法
        this.computeSystemTime();   //系统缺省时间计算方法
    },
    methods: {
        //时钟时间更新方法
        updateTime: function () {
            //获取时间
            var date = new Date();
            //给时间赋值
            this.default_time.hour = date.getHours() < 10 ? '0' + date.getHours() : date.getHours();
            this.default_time.minute = date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes();
            this.default_time.second = date.getSeconds() < 10 ? '0' + date.getSeconds() : date.getSeconds();
            //给日期赋值
            this.default_date = date.getFullYear() + '-' +
                    ((date.getMonth() + 1) <= 9 ? '0' : '') + (date.getMonth() + 1) + '-' +
                    (date.getDate() <= 9 ? '0' : '') + date.getDate();
            //给星期赋值
            this.default_week = date.getDay() === 0 ? this.week_day[0] :
                date.getDay() === 1 ? this.week_day[1] :
                    date.getDay() === 2 ? this.week_day[2] :
                        date.getDay() === 3 ? this.week_day[3] :
                            date.getDay() === 4 ? this.week_day[4] :
                                date.getDay() === 5 ? this.week_day[5] :
                                    date.getDay() === 6 ? this.week_day[6] : '';
            //给时区赋值
            this.default_time_zone = 'UTC' + (0 - date.getTimezoneOffset() / 60 >= 0 ? '+' : '') + (0 - date.getTimezoneOffset() / 60);
            //给系统的记录的终了时间赋值
            this.system_end_date = this.default_date;
            this.system_end_time = this.default_time.hour+':'+this.default_time.minute;
        },
        //获取当前时间方法
        getCurrentDate: function (val) {
            //获取时间
            let new_date = new Date();
            //val === 0 是开始时间 val === 1 是终了时间
            if (val === 0) {
                this.user_start_date = new_date.getFullYear() + '-' +
                        ((new_date.getMonth() + 1) <= 9 ? '0' : '') + (new_date.getMonth() + 1) + '-' +
                        (new_date.getDate() <= 9 ? '0' : '') + new_date.getDate();
                this.user_start_time = (new_date.getHours() < 10 ? '0' + new_date.getHours() : new_date.getHours()) + ':' +
                        (new_date.getMinutes() < 10 ? '0' + new_date.getMinutes() : new_date.getMinutes());
            } else if (val === 1) {
                this.user_end_date = new_date.getFullYear() + '-' +
                        ((new_date.getMonth() + 1) <= 9 ? '0' : '') + (new_date.getMonth() + 1) + '-' +
                        (new_date.getDate() <= 9 ? '0' : '') + new_date.getDate();
                this.user_end_time = (new_date.getHours() < 10 ? '0' + new_date.getHours() : new_date.getHours()) + ':' +
                        (new_date.getMinutes() < 10 ? '0' + new_date.getMinutes() : new_date.getMinutes());
            }
        },
        //计算时间方法
        computeTime: function (start_date, start_time, end_date, end_time) {
            // computeTime(开始日期,开始时间,结束日期,结束时间)
            // computeTime方法的，形参格式约定（2020-10-11,18:18）
            // Date.UTC(年，月，日，时，分)得到的结果时毫秒数
            // 返回 结束时间 减去 开始时间 的 分钟数
            // let s_date = Date.UTC(start_date.slice(0, 4), start_date.slice(5, 7), start_date.slice(8, 10), start_time.slice(0, 2), start_time.slice(3, 5));
            // let e_date = Date.UTC(end_date.slice(0, 4), end_date.slice(5, 7), end_date.slice(8, 10), end_time.slice(0, 2), end_time.slice(3, 5));
            // return (e_date - s_date) / 1000 / 60;
            let s_date = new Date(start_date + ' ' + start_time).getTime();
            let e_date = new Date(end_date + ' ' + end_time).getTime();
            return (e_date - s_date) / 1000 / 60;
        },
        //计算系统记录的工时
        computeSystemTime: function () {
            //获取事件对象
            let new_date = new Date();
            //获取当前日期
            let current_date = new_date.getFullYear() + '-' +
                    ((new_date.getMonth() + 1) <= 9 ? '0' : '') + (new_date.getMonth() + 1) + '-' +
                    (new_date.getDate() <= 9 ? '0' : '') + new_date.getDate();
                //获取当前时间
            let current_time = (new_date.getHours() < 10 ? '0' + new_date.getHours() : new_date.getHours()) + ':' +
                    (new_date.getMinutes() < 10 ? '0' + new_date.getMinutes() : new_date.getMinutes());
                //执行计算方法,并赋值给系统记录时间
            this.system_timekeeping = Number(this.computeTime(this.system_start_date, this.system_start_time, current_date, current_time));
            //计算工时差异
            this.count_difference();
        },
        //计算用户记录的工时
        computeTimekeeping: function () {
            //console.log('计算用户记录的工时',this.user_start_date,this.user_start_time,this.user_end_date,this.user_end_time);
            if (this.user_start_date != '' && this.user_start_time != '' && this.user_end_date != '' && this.user_end_time != '') {
                this.user_timekeeping = this.computeTime(this.user_start_date, this.user_start_time, this.user_end_date, this.user_end_time);
            }
            //计算工时差异
            this.count_difference();
        },
        //清空作业时间
        clearWorkTime: function (){
            this.user_start_date = '';
            this.user_start_time = '';
            this.user_end_date = '';
            this.user_end_time = '';
            this.user_timekeeping = 0;
        },
        count_difference: function (){
            this.timekeeping_difference = this.user_timekeeping - this.system_timekeeping;
            if (this.end_date_disabled && this.isSaved){
                this.savedRealTime.user_end_date = this.user_end_date;
                this.savedRealTime.user_end_time = this.user_end_time;
                this.savedRealTime.user_timekeeping = this.user_timekeeping;
            }
        },
        //保存时间
        saveTime(){
            //验证时间组件时间必填
            if ((this.user_start_date != '' && this.user_start_time != '') || (this.user_start_date == '' && this.user_start_time == '')){
                if (!((this.user_end_date != '' && this.user_end_time != '') || (this.user_end_date == '' && this.user_end_time == ''))) {
                    this.$refs.alert.show('作業時間の手動設定日時を完備してください。');
                    return false;
                } else {
                    if (!/^[0-9]\d*$/g.test(this.user_timekeeping)) {
                        this.$refs.alert.show('作業工数が正しくありません。');
                        return false;
                    } else {
                        if (this.user_timekeeping > 599940) {
                            this.$refs.alert.show('作業工数が正しくありません。');
                            return false;
                        }
                    }
                }
            } else {
                this.$refs.alert.show('作業時間の手動設定日時を完備してください。');
                return false;
            }
            this.savedRealTime.user_timekeeping = this.user_timekeeping;
            this.savedRealTime.user_start_date = this.user_start_date;
            this.savedRealTime.user_start_time = this.user_start_time;
            this.savedRealTime.user_end_date = this.user_end_date;
            this.savedRealTime.user_end_time = this.user_end_time;
            this.savedRealTime.end_date_disabled = this.end_date_disabled;
            this.savedRealTime.remark = this.remark;
            this.isSaved = true;
            this.dialog = false;
        },
        //打开弹窗
        dialogOpenT(){
            this.dialog = true;
            this.isSaved = false;
            //默认赋上一次保存的值
            this.user_timekeeping = this.savedRealTime.user_timekeeping;
            this.user_start_date = this.savedRealTime.user_start_date
            this.user_start_time = this.savedRealTime.user_start_time;
            this.user_end_date = this.savedRealTime.user_end_date;
            this.user_end_time = this.savedRealTime.user_end_time;
            this.end_date_disabled  = this.savedRealTime.end_date_disabled;
            this.remark = this.savedRealTime.remark;
        },
        //点击link
        showPdfLinkA(){
            let type = '';
            const file = {
                name: '時間記録説明',
                file_path: 'manuals/Common/時間記録説明.pdf',
            }
            this.$refs.filePreviewDialog.show([file], [], type);
        }
    }
}
</script>

<style scoped lang="scss">
    .col-3 {
        -ms-flex: 0 0 25%;
        flex: 0 0 25%;
        max-width: 25%;
    }
    .ml-6{
        margin-left: 10px;
    }
    /* 字体引入 */
    @font-face {
        font-family: 'Share Tech Mono';
        font-style: normal;
        font-weight: 400;
        /*src: local('Share Tech Mono'), local('ShareTechMono-Regular'), url('../../../../../../../public/fonts/date_buss_t_font.woff2') format('woff2');*/
        unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
    }
    .v-card__actions {
        padding: 18px;
    }
    .v-menu--inline{
        display: block;
    }
    .spacer{
        position: relative;
        .projection{
            ::v-deep .v-input__control{
                .v-input__slot{
                    margin-bottom: 8px;
                    label{
                        font-size: inherit;
                    }
                    input{
                        font-size: inherit;
                        color: rgba(0,0,0,.87);
                    }
                }
            }
            .system-time-box{
                box-shadow: 1px 1px 4px rgba(0, 0, 0, 0.24);
                background-color: #f0f0f0;
                .item-title{
                    color: #666666;
                    font-weight: 700;
                    line-height: 22px;
                    padding-left: 6px;
                    border-left: solid 4px #666666;
                }
                .text-color{
                    margin-bottom: 0;
                    ::v-deep .v-input__control{
                        .v-input__slot{
                            .v-text-field__slot{
                                input{
                                    font-weight: bold;
                                    color: #666666 !important;
                                }
                            }
                        }
                    }
                }
            }
            .default-time-box{
                position: relative;
                top: 0;
                bottom: 0;
                left: 0;
                right: 0;
                margin: auto auto;
                .tips{
                    display: block;
                    font-size: 12px;
                    color: #7f7f7f;
                    white-space: nowrap;
                    transform: translateY(8px);
                    text-align: center;
                }
                .clock-box{
                    width: 222px;
                    height: 222px;
                    padding: 6px;
                    margin: 0 auto;
                    border-radius: 50%;
                    box-shadow: 0 1px 5px rgba(0, 0, 0, 0.4);
                    position: relative;
                    img{
                        width: 92%;
                        opacity: 0.6;
                        border: none;
                        border-radius: 50%;
                        position: absolute;
                        top: 0;
                        bottom: 0;
                        left: 0;
                        right: 0;
                        margin: auto auto;
                    }
                    ::v-deep .v-progress-circular__info{
                        width: 100%;
                        height: 100%;
                    }
                    .time-box {
                        position: absolute;
                        width: 210px;
                        top: 76px;
                        p {
                            margin: 0;
                            text-align: center;
                            color: #555555;
                        }
                        .date {
                            font-size: 12px;
                            line-height: 12px;
                            padding-bottom: 4px;
                        }
                        .time {
                            font-family: 'Share Tech Mono', monospace;
                            font-size: 32px;
                            font-weight: bold;
                            line-height: 40px;
                        }
                        .time-zone {
                            font-size: 12px;
                            line-height: 16px;
                        }
                    }
                }
                .timekeeping{
                    position: relative;
                    box-shadow: 0 1px 5px rgba(0, 0, 0, 0.3);
                    width: 210px;
                    padding: 20px 0;
                    margin: 20px auto 0 auto;
                    transition: all 0.2s;
                    div{
                        display: flex;
                        justify-content: center;
                        position: relative;
                        input{
                            outline: none;
                            font-family: 'Share Tech Mono', monospace;
                            font-size: 28px;
                            font-weight: bold;
                            height: 32px;
                            line-height: 32px;
                            text-align: center;
                            color: #555555;
                            border-bottom: solid 1px #999999;
                        }
                        span{
                            font-family: 'Share Tech Mono', monospace;
                            font-size: 28px;
                            color: #666666;
                            position: absolute;
                            right: 45px;
                            top: 8px;
                        }
                    }
                    p{
                        margin-bottom: 0;
                        text-align: center;
                        font-size: 14px;
                    }
                }
                .increase{
                    box-shadow: 0 1px 8px rgba(200, 0, 0, 0.3);
                    div{
                        input{
                            color: #fc5050;
                            border-color: #fc5050;
                        }
                        span{
                            color: #fc5050;
                        }
                    }
                    p{
                        color: #fc5050;
                    }
                }
                .reduce{
                    box-shadow: 0 1px 8px rgba(0, 160, 0, 0.3);
                    div{
                        input{
                            color: #3cd250;
                            border-color: #3cd250;
                        }
                        span{
                            color: #3cd250;
                        }
                    }
                    p{
                        color: #3cd250;
                    }
                }
            }
            .user-time-box{
                box-shadow: -1px 1px 4px rgba(0, 0, 0, 0.24);
                text-align: left;
                .item-title{
                    color: #666666;
                    font-weight: bold;
                    line-height: 22px;
                    padding-left: 6px;
                    border-left: solid 4px #00BF99;
                    b{
                        float: left;
                        transform: translateY(2px);
                    }
                    ::v-deep .v-input--switch{
                        float: right;
                        margin: 0;
                        padding: 0;
                        transform: translateY(2px);
                    }
                }
                .item-title::after{
                    display: block;
                    content: '';
                    clear: both;
                }
                .work-time{
                    ::v-deep .v-menu:first-child .v-text-field{
                        margin-top: 0;
                    }
                    position: relative;
                    span{
                        font-size: 12px;
                        font-weight: normal;
                        color: #00BF99;
                        cursor: pointer;
                        position: absolute;
                        top: -3px;
                        right: 0;
                        transform: scale(0.95, 0.95);
                    }
                }
                .item-title.mt-2{
                    ::v-deep .v-input__slot{
                        margin-bottom: 0px !important;
                    }
                }
                .disabled{
                    background-color: #f0f0f0;
                }
                .text-color{
                    margin-bottom: 8px;
                    ::v-deep .v-input__control{
                        .v-input__slot{
                            margin-bottom: 0;
                            .v-text-field__slot{
                                input{
                                    font-size: 22px;
                                    font-weight: bold;
                                    color: #00BF99 !important;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    .btn-box{
        position: relative;
        display: flex;
        justify-content: center;
        .link{
            position: absolute;
            left: 24px;
            font-size: 14px;
            color: #00BF99;
        }
    }
    .remark{
        padding: 0 20px;
        textarea{
            display: block;
            width: 100%;
            min-height: 50px;
            max-height: 150px;
            font-size: 14px;
            box-shadow: 0 0 3px rgba(0, 0, 0, 0.2);
            color: #333333;
            border-radius: 2px;
            padding: 10px;
            outline: none;
        }
        textarea:focus{
            box-shadow: 0 0 4px rgba(0, 120, 255, 0.6);
        }
    }
    .height_0{
        height: 0 !important;
        border-bottom: none;
    }
    .success{
        background-color: #4db6ac !important;
        border-color: #4db6ac !important;
    }
    .warning{
        background-color: #fb8c00 !important;
        border-color: #fb8c00 !important;
    }
    .v-card__title{
        padding: 16px 16px 8px !important;
    }
    .pt-3{
        padding-top: 12px !important;
    }
    .pa-5{
        padding: 20px !important;
    }
    .mx-4 {
        margin-right: 16px!important;
        margin-left: 16px!important;
    }
    .ml-6 {
        margin-left: 24px!important;
    }
    .mt-12 {
        margin-top: 48px!important;
    }
    .v-card__title {
        -webkit-box-align: center;
        -ms-flex-align: center;
        align-items: center;
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
        font-size: 1.5rem;
        font-weight: 400;
        letter-spacing: normal;
        line-height: 2.3rem;
        padding: 16px 16px 8px;
    }
    .pt-6{
        padding-top: 24px !important;
    }
    .pb-6{
        padding-bottom: 24px !important;
    }
    a{
        text-decoration: underline;
    }
    .item-title{
        font-size: 16px;
    }
    .mr-3{
        margin-right: 8px !important;
    }
    .ma-zero ::v-deep label{
        margin: 0 !important;
    }
</style>

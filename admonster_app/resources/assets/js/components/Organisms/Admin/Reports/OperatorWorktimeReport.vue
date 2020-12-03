<template>
    <v-layout row wrap>
        <v-flex xs12 md6>
            <v-card dark class="report-description">
                <v-card-text>
                    <section>
                        <h4>〇レポート概要</h4>
                        <p>日付×担当者×業務・作業の工程別に自動計測したシステム工数と、作業画面で入力した手動工数を確認できる。</p>
                    </section>
                    <hr>
                    <section>
                        <h4>〇システム工数の取得方法について</h4>
                        <ul>
                            <li>ADPORETER画面での操作毎に発生するhttpリクエストのログから、ユーザー毎にログの発生時刻の間隔を計算してシステム工数を算出する。</li>
                            <li>間隔が2時間以上となる場合は操作を停止したと判断して工数は20分として記録し、次回、操作を行ったタイミングから再度工数集計を開始する。</li>
                            <li>集計処理は1日1回、前日分のログを集計する。そのため、レポート出力できるのは前日分までの工数となる。</li>
                        </ul>
                    </section>
                    <hr>
                    <section>
                        <h4>〇各項目について</h4>
                        <ul>
                            <li>日付</li>
                            <p>システム工数が発生（ADPORTERを操作）した日</p>
                            <li>所属1/所属2/所属3</li>
                            <p>現在、出力されません</p>
                            <li>作業者名</li>
                            <p>システム工数が発生（ADPORTERを操作）した担当者の名前</p>
                            <li>業務名</li>
                            <p>システム工数が発生（ADPORTERを操作）した業務の名称</p>
                            <li>作業名</li>
                            <p>システム工数が発生（ADPORTERを操作）した作業の名称</p>
                            <li>工程</li>
                            <p>システム工数が発生（ADPORTERを操作）した作業により以下の工程に分類する。
                                <ul>
                                    <li>管理：案件一覧、業務一覧、業務詳細、依頼一覧、依頼詳細、進捗一覧</li>
                                    <li>取込：ファイル取込</li>
                                    <li>割振：割振一覧、個別割振、一括割振</li>
                                    <li>タスク：作業一覧、各作業画面</li>
                                    <li>承認：承認一覧、承認</li>
                                    <li>納品：納品一覧、納品</li>
                                    <li>検証：検証</li>
                                    <li>クライアント確認：クライアントページ</li>
                                    <li>他：ログイン</li>
                                </ul>
                            </p>
                            <li>件数</li>
                            <p>取込/割振/タスク/承認/納品/検証 工程を実施した件数<br>※一覧の参照や作業画面で処理せず戻った場合はカウントされません。</p>
                            <li>OK</li>
                            <p>承認でOKとなったタスクの件数、タスク工程にのみ発生</p>
                            <li>NG</li>
                            <p>承認でNGとなったタスクの件数、タスク工程にのみ発生</p>
                            <li>システム工数（分）</li>
                            <p>前述の内容で集計したシステム工数</p>
                            <li>手動工数（分）</li>
                            <p>作業画面で入力した手動工数、タスク工程にのみ発生</p>
                            <li>演習</li>
                            <p>〇：演習に関わる作業、無印は本番作業</p>
                        </ul>
                    </section>
                </v-card-text>
            </v-card>
        </v-flex>
        <v-flex xs12 md6>
            <div class="pa-2">
                <div class="d-flex align-center">
                    <date-picker
                        v-model="period.from"
                        append-icon="event"
                        :label="$t('reports.period.label') + $t('reports.period.from')"
                    ></date-picker>
                    <span style="text-align: center;max-width: 40px;">～</span>
                    <date-picker
                        v-model="period.to"
                        append-icon="event"
                        date-to
                        :label="$t('reports.period.label') + $t('reports.period.to')"
                    ></date-picker>
                </div>
                <autocomplete
                    v-model="selectedUsers"
                    chips
                    dense
                    item-avatar="image_path"
                    item-text="name"
                    item-value="id"
                    :items="candidates"
                    :label="$t('reports.operator.label')"
                    multiple
                    :no-data-text="$t('common.validation.custom.select.no_data_text')"
                    slot-prepend-item-type="all"
                    slot-selection-type="image-and-text"
                    :placeholder="$t('reports.operator.placeholder')"
                ></autocomplete>
                <div>
                    <v-btn :disabled="!canOutput" color="primary" @click="output()">{{ $t('common.button.output') }}</v-btn>
                </div>
            </div>
        </v-flex>
        <progress-circular v-if="loading"></progress-circular>
        <alert-dialog ref="alert"></alert-dialog>
        <confirm-dialog ref="confirm"></confirm-dialog>
    </v-layout>
</template>

<script>
import DatePicker from '../../../Atoms/Pickers/DatePicker'
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'
import Autocomplete from '../../../Atoms/Autocompletes/Autocomplete'
import ConfirmDialog from '../../../Atoms/Dialogs/ConfirmDialog'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'

export default {
    components: {
        AlertDialog,
        Autocomplete,
        ConfirmDialog,
        ProgressCircular,
        DatePicker
    },
    props: {
        candidates: {type: Array, required: false, default: () => []},
    },
    data: () => ({
        loading: false,
        period: {from: '', to: ''},
        reportType: 'operatorReport',
        selectedUsers: [],
    }),
    computed: {
        canOutput () {
            // 期間項目が入力されているか
            if (this.period.from === '' || this.period.to === '') return false
            // 担当者項目が入力されているか
            if (this.selectedUsers.length === 0) return false
            return true
        },
    },
    methods: {
        loadingDisplay (bool) {
            this.loading = bool
        },
        async output () {
            if (await this.$refs.confirm.show(Vue.i18n.translate('reports.message.output'))) {
                try {
                    this.loadingDisplay(true)

                    let formData = new FormData()
                    formData.append('reportType',this.reportType)
                    this.selectedUsers.forEach(function (userId) {
                        formData.append('userIds[]',userId)
                    })

                    formData.append('period', JSON.stringify(this.period))
                    const res = await axios.post('/api/reports/output', formData)

                    this.loadingDisplay(false)

                    if (res.data.result === 'success') {
                        // 非同期処理なので成功メッセージは無いと思われる
                        // this.$refs.alert.show(Vue.i18n.translate('common.message.saved'))
                        const isNullOrUndefined = (val) => val === null || val === undefined
                        const fileName = isNullOrUndefined(res.data['file_name']) ? '' : res.data['file_name']// file.name
                        const filePath = isNullOrUndefined(res.data['file_path']) ? '' : res.data['file_path']// file.path
                        const link = document.createElement('a')
                        let uri = '/api/utilities/downloadFromLocal?'
                        uri += `file_name=${encodeURIComponent(fileName)}&`
                        uri += `file_path=${encodeURIComponent(filePath)}`
                        link.href = uri
                        // ファイル名が空文字列の場合、urlの末の文字列が入る問題を修正
                        const downloadFailureFileName = fileName === '' ? 'notFileName' : fileName // DL失敗時の表示ファイル名
                        link.setAttribute('download', downloadFailureFileName)
                        document.body.appendChild(link)
                        link.click()
                        document.body.removeChild(link)
                    } else {
                        this.$refs.alert.show(Vue.i18n.translate('common.message.internal_error'))
                    }
                } catch (err) {
                    console.log(err)
                    this.loadingDisplay(false)
                    this.$refs.alert.show(Vue.i18n.translate('common.message.internal_error'))
                }
            }
        },
    }
}
</script>
<style lang="scss" scoped>
.report-description {
    height: 76vh;
    overflow-y: auto;

    h4 {
        font-weight: bold;
    }

    p {
        text-indent: 1em;
    }

    ul {
        list-style: disc;
    }
}
</style>

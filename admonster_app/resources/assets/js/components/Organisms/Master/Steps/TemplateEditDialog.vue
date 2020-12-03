<template>
    <v-dialog v-model="dialog" persistent width="580">
        <v-card>
            <v-card-title class="headline" primary-title>{{ $t('master.steps.template_edit.title') }}</v-card-title>
            <v-card-text>
                <!-- template header -->
                <!-- <div @click.stop="edit('header')"> -->
                <div>
                    <span v-if="editableHeader">
                        <v-textarea
                            solo
                            v-model="templateHeader"
                        ></v-textarea>
                    </span>
                    <span v-else>
                        <div v-html="templateHeader"></div>
                    </span>
                    <div class="text-xs-right" @click="editableHeader = !editableHeader">
                        <a>{{ editableHeader ? $t('master.steps.check_display') : $t('master.steps.edit_header') }}</a>
                    </div>
                </div>
                <!-- /template header -->
                <!-- template body -->
                <div v-if="Object.keys(itemConfigs).length > 0" class="pa-3">
                    <template v-for="item in itemConfigs">
                        <span :key="item['id']">
                            <v-checkbox
                                color="primary"
                                hide-details
                                :input-value="item['use_screen_type_delivery_flag']"
                                :label="label(item['label_id'])"
                                @change="useScreenFlag(item, $event)"
                            ></v-checkbox>
                        </span>
                    </template>
                </div>
                <!-- /template body -->
                <!-- template footer -->
                <!-- <div @click.stop="edit('footer')"> -->
                <div>
                    <span v-if="editableFooter">
                        <v-textarea
                            solo
                            v-model="templateFooter"
                        ></v-textarea>
                    </span>
                    <span v-else>
                        <div v-html="templateFooter"></div>
                    </span>
                    <div class="text-xs-right" @click="editableFooter = !editableFooter">
                        <a>{{ editableFooter ? $t('master.steps.check_display') : $t('master.steps.edit_footer') }}</a>
                    </div>
                </div>
                <!-- /template footer -->
            </v-card-text>

            <!-- 処理ボタン -->
            <v-card-actions class="justify-center">
                <v-btn @click="close()">{{ $t('common.button.cancel') }}</v-btn>
                <v-btn color="primary" @click="save()">{{ $t('common.button.save') }}</v-btn>
            </v-card-actions><!-- / 処理ボタン -->
            <progress-circular v-if="loading"></progress-circular>
            <notify-modal></notify-modal>
            <!-- <editor-dialog ref="editorDialog"></editor-dialog> -->
        </v-card>
    </v-dialog>
</template>

<script>
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import NotifyModal from '../../../Atoms/Dialogs/NotifyModal'
// import EditorDialog from '../../../Atoms/Dialogs/EditorDialog'

export default {
    props: {
        labels: { type: Object, require: false, default: () => {} },
    },
    data: () => ({
        dialog: false,
        loading: false,
        step: {},
        itemConfigs: [],
        template: {
            before_work: {
                header: '',
                footer: ''
            },
            mail: {
                header: '',
                footer: ''
            }
        },
        editableHeader: false,
        editableFooter: false,
    }),
    components: {
        ProgressCircular,
        NotifyModal,
        // EditorDialog,
    },
    computed: {
        selectedTemplateType () {
            /*
             * NOTE: 前工程があればbefore_work／それ以外はmail（fileは使うか微妙だったので一旦追加しない）
             */
            /*
             * TODO: 前工程作業内容と依頼メール（または納品メール？）
             * どちらも表示させたいと要望があった場合はセレクトボックスを追加してどちらも設定できるようにする
             */
            return Object.keys(this.itemConfigs).length > 0 ? 'before_work' : 'mail'
        },
        templateHeader: {
            set (val) {
                this.template[this.selectedTemplateType]['header'] = val
            },
            get () {
                return this.template[this.selectedTemplateType]['header']
            }
        },
        templateFooter: {
            set (val) {
                this.template[this.selectedTemplateType]['footer'] = val
            },
            get () {
                return this.template[this.selectedTemplateType]['footer']
            }
        }
    },
    methods: {
        // async edit (key) {
        //     // 本当はエディタダイアログで編集したいがエディタ側の調整が間に合っていないので一旦使わない
        //     if (key === 'header') {
        //         const {value, isEnter} = await this.$refs.editorDialog.show(this.templateHeader)
        //         if (isEnter) this.templateHeader = value
        //     } else if (key === 'footer') {
        //         const {value, isEnter} = await this.$refs.editorDialog.show(this.templateFooter)
        //         if (isEnter) this.templateFooter = value
        //     } else {
        //         console.error('Unknown template object key: ' + key)
        //     }
        // },
        useScreenFlag (itemConfig, useFlag=true) {
            itemConfig['use_screen_type_delivery_flag'] = useFlag ? _const.FLG.ACTIVE : _const.FLG.INACTIVE
        },
        show (step) {
            this.step = step
            this.dialog = true
            this.template = Object.assign(this.template, JSON.parse(this.step['request_screen_template']))
            this.setData()
        },
        save () {
            this.loading = true

            let formData = new FormData();
            formData.append('step_id', this.step['id'])
            formData.append('template', JSON.stringify(this.template))
            formData.append('item_configs', JSON.stringify(this.itemConfigs))
            axios.post('/api/master/steps/updateRequestTemplate', formData)
                .then((res) => {
                    // ポップアップメッセージ
                    let message = this.$t('common.message.internal_error')
                    if (res.data.result == 'success') {
                        message = this.$t('common.message.saved')
                        // propsデータ更新
                        this.$emit('set-step', res.data.step)
                    }
                    // ポップアップ表示
                    eventHub.$emit('open-notify-modal', { message: message})
                    // ダイアログ閉じる
                    this.close()
                })
                .catch((err) => {
                    console.log(err)
                    eventHub.$emit('open-notify-modal', { message: this.$t('common.message.internal_error') })
                })
                .finally(() => {
                    this.loading = false
                });
        },
        close () {
            Object.assign(this.$data, this.$options.data())
        },
        setData:async function () {
            this.loading = true
            axios.get('/api/master/steps/getItemConfigs', {
                params: {
                    step_id: this.step.id,
                }
            })
                .then((res) => {
                    this.itemConfigs = res.data.list
                })
                .catch((err) => {
                    console.log(err)
                })
                .finally(() => {
                    this.loading = false
                });
        },
        label (labelId) {
            const langCode = Vue.i18n.locale()
            return this.labels[langCode][labelId] ? this.labels[langCode][labelId] : ''
        },
    }
}
</script>

<style scoped>
.v-input.v-input--selection-controls {
    margin-top: 0;
    padding-top: 4px;
}
</style>

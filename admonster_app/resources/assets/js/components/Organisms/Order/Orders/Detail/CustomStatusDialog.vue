<template>
    <!-- 確認モーダル -->
    <div id="custom-status-dialog-block" v-if="modal">
        <v-layout row justify-center>
            <v-dialog v-model="modal" persistent max-width="800px">
                <v-form ref="form" @submit.prevent v-model="valid">
                    <v-card class="custom-status">
                        <v-card-title class="pa-3" style="border-bottom: 1px solid #d7d7d7;">
                            <span style="font-size:20px!important;" class="headline" v-if="forKey === null">
                                {{ $t('order.orders.setting.custom_status.dialog.add_custom_status') }}
                            </span>
                            <span style="font-size:20px!important;" class="headline" v-else>
                                {{ $t('order.orders.setting.custom_status.dialog.edit_custom_status') }}
                            </span>
                            <v-spacer></v-spacer>
                            <v-btn flat icon small class="ma-0" @click="close()">
                                <v-icon>mdi-close</v-icon>
                            </v-btn>
                        </v-card-title>
                        <v-flex class="ma-3">
                            <span style="font-size: 16px;">{{ $t('order.orders.setting.custom_status.dialog.custom_status_name') }}</span>
                            <v-text-field
                                class="ma-2 pa-0"
                                v-model="customStatus.customStatusName"
                                :counter="maxLength"
                                :rules="rules"
                                clearable
                            ></v-text-field>
                            <div class="mt-4 mr-0 mb-2 ml-0" style="border: 1px solid #f2f2f2">
                                <v-container fluid class="pt-0 pr-0 pb-0 pl-3" style="border-bottom: 1px solid #d7d7d7;">
                                    <v-layout wrap align-center>
                                        <span>{{ $t('order.orders.setting.custom_status.dialog.attribute_setting') }}</span>
                                        <v-spacer></v-spacer>
                                        <v-tooltip top>
                                            <v-btn
                                                flat
                                                icon
                                                color="primary"
                                                slot="activator"
                                                @click="addAttribute"
                                            >
                                                <v-icon>mdi-plus-circle</v-icon>
                                            </v-btn>
                                            <span>{{ $t('order.orders.setting.custom_status.dialog.add_attribute') }}</span>
                                        </v-tooltip>
                                    </v-layout>
                                </v-container>
                                <div class="mt-3">
                                    <draggable :list="customStatus.attributes" :options="{ animation: 300, handle: '.handle' }">
                                        <li
                                            class="list-group-item ma-3"
                                            style="overflow: auto; border-radius: unset; border: 1px solid #f2f2f2"
                                            v-for="attribute in customStatus.attributes"
                                            :key="attribute.forKey"
                                        >
                                        <span style="float: left; margin-top: 5px;" class="handle">
                                            <v-icon>mdi-menu</v-icon>
                                        </span>
                                            <v-text-field
                                                class="pt-0"
                                                style="float: left; min-width: 600px;"
                                                v-model="attribute.name"
                                                :counter="maxLength"
                                                :rules="rules"
                                            ></v-text-field>
                                            <v-tooltip top>
                                                <v-btn
                                                    small
                                                    icon
                                                    slot="activator"
                                                    class="mt-3"
                                                    style="float: right; color: rgba(0, 0, 0, .54);"
                                                    @click="deleteAttribute(attribute.forKey, attribute.isUseCustomStatusAttribute)"
                                                >
                                                    <v-icon>close</v-icon>
                                                </v-btn>
                                                <span>{{ $t('order.orders.setting.custom_status.dialog.delete_attribute') }}</span>
                                            </v-tooltip>
                                        </li>
                                    </draggable>
                                </div>
                            </div>
                        </v-flex>
                        <v-card-actions class="btn-center-block pb-3">
                            <v-btn color="grey" large dark @click="close" class="mr-3" style="width: 120px;">{{ $t('common.button.cancel') }}</v-btn>
                            <v-btn color="primary" large :disabled="isDisabled" @click="save()" style="width: 120px;">{{ $t('common.button.setting') }}</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-form>
            </v-dialog>
        </v-layout>
        <confirm-dialog ref="confirm"></confirm-dialog>
    </div>
    <!-- / 確認モーダル -->
</template>

<script>
import store from '../../../../../stores/Order/Orders/Detail/store'
import ConfirmDialog from '../../../../Atoms/Dialogs/ConfirmDialog'

export default {
    components: {
        ConfirmDialog,
    },
    data () {
        return {
            valid: false,
            modal: false,
            forKey: null,

            customStatus: {
                forKey: null,
                customStatusId: null,
                customStatusName: '',
                attributes: [],
                labelId: null,
                isUseCustomStatus: 0
            },
            deleteAttributeIds: []
        }
    },
    computed: {
        maxLength () {
            return 20
        },
        rules () {
            let rules = [
                v => (v !== null && v.length > 0) || this.$t('order.orders.setting.custom_status.dialog.error.no_text'), // min
                v => (v !== null && v.length <= this.maxLength) || this.$t('order.orders.setting.custom_status.dialog.error.limit_text', { number: this.maxLength }), // max
            ]
            return rules
        },
        isCreateMode: function() {
            return this.customStatus === undefined
        },
        isDisabled: function() {
            return !this.valid
        },
        attributes: function() {
            return this.customStatus.attributes
        },
        forKeyCustomStatus: function() {
            return store.state.processingData.temporaryForKey.customStatus
        },
        forKeyAttribute: function() {
            return store.state.processingData.temporaryForKey.attribute
        },
    },
    watch: {
        attributes: {
            async handler() {
                await this.$nextTick()
                this.$refs.form.validate()
            },
        },
    },
    methods: {
        save: async function() {
            if (this.forKey === null) {
                await store.dispatch('addCustomStatus', this.customStatus) // 追加
            } else {
                await store.dispatch('updateCustomStatus', { customStatus: this.customStatus, deleteAttributeIds: this.deleteAttributeIds }) // 更新
            }
            await this.$emit('save')
            this.close()
        },
        deleteAttribute: async function(forKey, isUseCustomStatusAttribute) {
            // 編集の場合のみ削除確認ダイアログ表示
            if (this.forKey !== null) {
                const messages = []
                if (isUseCustomStatusAttribute) messages.push(this.$t('order.orders.setting.custom_status.message.status_used_in_order_detail'))
                if (isUseCustomStatusAttribute) messages.push(this.$t('order.orders.setting.custom_status.message.deleting_after_unselected'))
                messages.push(this.$t('order.orders.setting.custom_status.message.delete'))
                const message = messages.join('<br>')
                if (!(await this.$refs.confirm.show(message))) return
                if (isUseCustomStatusAttribute) {
                    if (!await this.$refs.confirm.show(`<h4>${this.$t('order.orders.setting.custom_status.message.last_confirmation')}</h4>
                        <div style="color: red;">※${this.$t('order.orders.setting.custom_status.message.deleting_after_unselected')}</div>
                        <div style="color: red;">${this.$t('order.orders.setting.custom_status.message.delete_last_confirmation')}</div>`,
                    this.$t('common.button.delete'))) return
                }
            }

            const deleteAttributeId = this.customStatus.attributes.find(item => item.forKey === forKey)['id']
            if (deleteAttributeId !== null) this.deleteAttributeIds.push(deleteAttributeId) // deleteAttributeId !== nullはDB登録済み

            this.customStatus.attributes = this.customStatus.attributes.filter(item => item.forKey !== forKey)
        },
        open: function(forKey = null) {
            this.clear()
            if (forKey !== null) {
                this.forKey = forKey
                const newCustomStatuses = JSON.parse(JSON.stringify(store.state.processingData.customStatuses))
                this.customStatus = newCustomStatuses.find(customStatus => customStatus.forKey === this.forKey)
            } else {
                // 追加の場合は、属性を2つ表示する
                for (let count = 0; count < 2; count++) {
                    this.addAttribute()
                }
            }
            this.modal = true
        },
        close: function() {
            this.modal = false
        },
        clear: function() {
            this.forKey = null
            store.commit('addForKeyCustomStatus')
            this.customStatus = { forKey: this.forKeyCustomStatus, customStatusId: null, customStatusName: '', attributes: [], labelId: null, isUseCustomStatus: 0 }
            this.deleteAttributeIds = []
        },
        addAttribute: function() {
            store.commit('addForKeyAttribute')
            this.customStatus.attributes.push({ id: null, name: '', forKey: this.forKeyAttribute, labelId: null, isUseCustomStatusAttribute: 0 })
        },
    },
}
</script>

<style scoped>
.sortable-ghost {
    background-color: #dcdcdc;
}
.text-color {
    color: rgba(0, 0, 0, 0.87);
}
.cursor-pointer {
    cursor: pointer;
}
.sortable-ghost {
    background-color: #dcdcdc;
}
.handle {
    color: rgba(0, 0, 0, 0.54);
    padding: 10px;
    cursor: move;
}
</style>

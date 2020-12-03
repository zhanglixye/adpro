<template>
    <div class="elevation-1">
        <common-header
            :title="$t('order.order_details.show.information_component_management.order_detail_info.title')"
            :height="headerHeight"
            :mode="mode"
            :full-width="fullWidth"
            :hide-left-grow-button="hideLeftGrowButton"
            :hide-right-grow-button="hideRightGrowButton"
            :hide-other-button="hideOtherButton"
            @previous="previous"
            @next="next"
            @shrink-right="shrinkRight"
            @shrink-left="shrinkLeft"
            @grow="grow"
            @to-edit-mode="toEditMode"
            @open-create-mail="openCreateMail"
            @open-create-request="openCreateRequest"
        >
        </common-header>
        <div
            :style="{
                'height': contentHeight,
                'overflow': 'auto',
                'padding-left': '16px',
                'padding-bottom': '16px',
                'padding-right': '16px',
                'white-space': 'nowrap',
            }"
        >
            <div class="pt-2" style="text-align: right;">
                <v-tooltip top>
                    <v-btn
                        icon
                        small
                        color="primary"
                        slot="activator"
                        @click="switchOrderDetailInfo"
                    >
                        <v-icon>mdi-repeat</v-icon>
                    </v-btn>
                    <span v-if="isAllOrderDetailInfo">
                        {{ $t('order.order_details.show.information_component_management.order_detail_info.display_to_order_detail_list_items') }}
                    </span>
                    <span v-else>
                        {{ $t('order.order_details.show.information_component_management.order_detail_info.display_to_all_items') }}
                    </span>
                </v-tooltip>
            </div>
            <all-order-detail-info
                v-show="isAllOrderDetailInfo"
                :isEditMode="isEditMode"
                :updatedSubjectData.sync="updatedSubjectData"
                :updatedCustomStatuses="deepCopyWithJSON(updatedCustomStatuses)"
                :updatedOrderDetail="deepCopyWithJSON(updatedOrderDetail)"
                :updateSelectAttributeId="updateSelectAttributeId"
                :updateColumnValue="updateColumnValue"
                :displayItemValid.sync="displayItemValid"
                :valid.sync="valid"
                :isSubjectAutogeneration="isSubjectAutogeneration"
                @save="save"
                @cancel="cancel"
                @switch-subject-autogeneration="switchSubjectAutogeneration"
            ></all-order-detail-info>
            <order-detail-info-by-order-detail-list
                v-show="!isAllOrderDetailInfo"
                :updatedSubjectData.sync="updatedSubjectData"
                :updatedCustomStatuses="deepCopyWithJSON(updatedCustomStatuses)"
                :updatedOrderDetail="deepCopyWithJSON(updatedOrderDetail)"
                :updateSelectAttributeId="updateSelectAttributeId"
                :updateColumnValue="updateColumnValue"
                :isEditMode="isEditMode"
                :displayItemValid.sync="displayItemValid"
                :valid.sync="valid"
                :isSubjectAutogeneration="isSubjectAutogeneration"
                @update-order-detail-is-active="updateOrderDetailIsActive"
                @save="save"
                @cancel="cancel"
                @switch-subject-autogeneration="switchSubjectAutogeneration"
            ></order-detail-info-by-order-detail-list>
        </div>
        <confirm-dialog ref="confirm"></confirm-dialog>
        <alert-dialog ref="alert"></alert-dialog>
    </div>
</template>

<script>
// Components
import ConfirmDialog from '../../../Atoms/Dialogs/ConfirmDialog'
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'
import CommonHeader from '../../../Molecules/Order/OrderDetails/CommonHeader'
import AllOrderDetailInfo from './AllOrderDetailInfo'
import OrderDetailInfoByOrderDetailList from './OrderDetailInfoByOrderDetailList'

// Mixins
import circleComponentMixin from '../../../../mixins/Order/OrderDetail/circleComponentMixin'

// Stores
import store from '../../../../stores/Order/OrderDetails/Show/store'

export default {
    mixins: [
        circleComponentMixin,
    ],
    components: {
        ConfirmDialog,
        AlertDialog,
        CommonHeader,
        AllOrderDetailInfo,
        OrderDetailInfoByOrderDetailList
    },
    data() {
        return {
            rules: {
                numCheck: v => {
                    v = v.toString().replace(/,/g, '')
                    const font = /[^\d-.]/.test(v)
                    if (font) {
                        // 文字
                        return Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.number_data_entry_rule.error_message.contain_text')
                    } else {
                        const minus = /-+/.test(v)
                        if (minus) {
                            const minusCount = v.match(/-/g).length
                            if (minusCount > 1) {
                                // 複数
                                return Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.number_data_entry_rule.error_message.minus_included_multiple')
                            } else {
                                const minusPosition = /^-/.test(v)
                                if (!minusPosition) {
                                    // 位置
                                    return Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.number_data_entry_rule.error_message.minus_position_is_different')
                                }
                            }
                            if (v.length <= 1) return Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.number_data_entry_rule.error_message.not_input_number')
                            return Number(v) >= -999999999999 || Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.number_data_entry_rule.error_message.less_than_minimum_value')
                        }
                    }
                    return Number(v) <= 999999999999 || Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.number_data_entry_rule.error_message.exceeded_maximum_value')
                },
                check: v => v.length <= 256 || Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.text_data_entry_rule.error_message.limit_order_detail_name', { number: 256 }),
                textMaxCheck: v => v.length <= 2000 || Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.text_data_entry_rule.error_message.limit_display_name', { number: 2000 }),
                required: v => v !== '' || Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.text_data_entry_rule.error_message.no_order_detail_name')
            },
            valid: false,
            displayItemValid: false,
            updatedSubjectData: '',
            updatedCustomStatuses: [],
            updatedOrderDetail: {},
            // 新規追加時は「すべて表示」パターンを初期表示する
            isAllOrderDetailInfo: store.state.processingData.orderId !== 0,
            isSubjectAutogeneration: true,
            comparisonSubjectAuto: ''
        }
    },
    computed: {
        orderId() {
            return store.state.processingData.orderId
        },
        orderDetailId() {
            return store.state.processingData.orderDetailId
        },
        orderDetail() {
            return store.state.processingData.orderDetailData
        },
        subject() {
            return store.state.processingData.subjectData
        },
        customStatuses() {
            return store.state.processingData.customStatuses
        },
        hideOtherButton() {
            return this.orderDetailId === 0
        },
        columns() {
            return this.updatedOrderDetail['columns']
        },
        subjectAuto() {
            if (this.columns !== undefined) {
                const subjectColumns = Object.values(this.columns)// columnsの配列を生成
                    .filter(column => column.subject_part_no !== null && column.value !== '')// 件名に含まれないかつ、値が設定されていないカラムは除く
                    .sort((a, b) => {
                        if (a.subject_part_no < b.subject_part_no) return -1
                        if (a.subject_part_no > b.subject_part_no) return 1
                        return 0
                    })// subject_part_noの昇順で並び替える
                return subjectColumns.map(column => column.value).join('_')// 項目の値から件名を生成
            } else {
                return ''
            }
        }
    },
    watch: {
        subject() {
            this.updatedSubjectData = store.state.processingData.subjectData
        },
        customStatuses() {
            this.updatedCustomStatuses = JSON.parse(JSON.stringify(this.customStatuses))
        },
        orderDetail() {
            this.updatedOrderDetail = JSON.parse(JSON.stringify(this.orderDetail))
        },
        isEditMode() {
            if (this.subjectAuto === this.subject) {
                this.isSubjectAutogeneration = true
            } else {
                this.isSubjectAutogeneration = false
            }
        },
        subjectAuto() {
            if (this.isSubjectAutogeneration && this.isEditMode) this.updatedSubjectData = this.subjectAuto
        },
    },
    methods: {
        switchOrderDetailInfo: async function() {
            if ((!this.isAllOrderDetailInfo && await this.$refs.confirm.show(this.$t('order.order_details.dialog.switch_order_detail_info.show_all_order_detail_info')))||
                (this.isAllOrderDetailInfo && await this.$refs.confirm.show(this.$t('order.order_details.dialog.switch_order_detail_info.show_order_detail_info')))
            ) {
                this.isAllOrderDetailInfo = !this.isAllOrderDetailInfo
            }
        },
        save: async function() {
            let message = ''
            if (this.orderDetailId === 0) {
                if (this.orderDetail.order_is_active === _const.FLG.INACTIVE) message += Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.is_order_inactive') + '<br>'
                message += Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.has_creating_newly')
            } else {
                if (this.orderDetail.order_is_active === _const.FLG.INACTIVE) message += Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.is_order_inactive') + '<br>'
                if (this.orderDetail.order_detail_is_active === _const.FLG.INACTIVE) message += Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.is_order_details_archive') + '<br>'
                message += Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.save')
            }

            if (await this.$refs.confirm.show(message)) {
                try {
                    await store.dispatch('saveOrderDetail', {
                        orderId: this.orderId,
                        orderDetailId: this.orderDetailId,
                        subjectData: this.updatedSubjectData,
                        orderDetailData: this.updatedOrderDetail,
                        customStatuses: this.updatedCustomStatuses,
                    })
                    // ダイアログ表示
                    this.$refs.alert.show(Vue.i18n.translate('common.message.saved'), () => {
                        if (this.orderDetailId === 0) {// 新規登録
                            if (window.history.length > 1) {
                                window.history.back()
                            } else {
                                window.location.href = '/order/order_details?order_id=' + this.orderId
                            }
                        } else {
                            this.toReadMode()
                        }
                    })
                } catch (error) {
                    console.log('error: ', error)
                    await this.$refs.alert.show(error)
                }
            }
        },
        cancel: function() {
            this.updatedSubjectData = this.subject
            this.updatedCustomStatuses = JSON.parse(JSON.stringify(this.customStatuses))
            this.updatedOrderDetail = JSON.parse(JSON.stringify(this.orderDetail))
            this.toReadMode()
        },
        showCounterIfEditing: function(count = true) {
            if (count === true) return true
            return this.isEditMode ? count : false
        },
        deepCopyWithJSON: function(object) {
            return JSON.parse(JSON.stringify(object))
        },
        updateSelectAttributeId: function(customStatusId, selectAttributeId) {
            const customStatus = this.updatedCustomStatuses.find(
                customStatus => customStatus.customStatusId === customStatusId
            )
            customStatus['selectAttributeId'] = selectAttributeId
        },
        updateColumnValue: function(index, value) {
            this.updatedOrderDetail['columns'][index]['value'] = value
        },
        updateOrderDetailIsActive: function(selectedSystemStatusId) {
            this.updatedOrderDetail['order_detail_is_active'] = selectedSystemStatusId
        },
        switchSubjectAutogeneration: function(switchSubjectAutogeneration) {
            this.isSubjectAutogeneration = switchSubjectAutogeneration
            if (switchSubjectAutogeneration) this.updatedSubjectData = this.subjectAuto
        }
    }
}
</script>

<style scoped>
</style>

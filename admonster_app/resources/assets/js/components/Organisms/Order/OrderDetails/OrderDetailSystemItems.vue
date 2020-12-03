<template>
    <div class="elevation-1">
        <common-header
            :title="$t('order.order_details.show.information_component_management.order_detail_system_items.title')"
            :height="headerHeight"
            :mode="mode"
            :full-width="fullWidth"
            :hide-left-grow-button="hideLeftGrowButton"
            :hide-right-grow-button="hideRightGrowButton"
            :hide-other-button="hideOtherButton"
            :hide-edit-button="hideEditButton"
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
            <div style="display: flex; margin-bottom: 10px;">
                <span style="padding-right: 15px;" class="pt-3">
                    {{ $t('order.order_details.show.information_component_management.order_detail_info.subject') }}
                </span>
                <!-- @submit.prevent：enter押下によるページリロードを防ぐ -->
                <div style="width: 100%;">
                    <v-form v-model="valid" ref="form" @submit.prevent>
                        <v-text-field
                            v-model="updatedSubjectData"
                            label
                            style="padding-top: 5px ;padding-right: 60px;"
                            :counter="showCounterIfEditing(256)"
                            :rules="[rules.check, rules.required]"
                            :disabled="!isEditMode"
                        ></v-text-field>
                    </v-form>
                </div>
            </div>
            <div>
                <div class="my-2">
                    {{ $t('order.order_details.show.information_component_management.order_detail_system_items.item') }}
                </div>
                <div class="each-item-content">
                    <span
                        style="max-width: 650px; display: flex; padding-top: 15px; padding-bottom: 15px;"
                    >
                        <v-icon
                            class="info-icon-size mt-1"
                        >mdi-wrench</v-icon>
                        <date-picker-without-buttons
                            v-if="importedAt === ''"
                            class="display-item"
                            style="width: 100%; padding-top: 5px;"
                            :label="$t('order.order_details.show.information_component_management.order_detail_system_items.created_at')"
                            placeholder="yyyy/mm/dd"
                            :dateValue="''"
                            :isActive="false"
                        ></date-picker-without-buttons>
                        <date-picker-without-buttons
                            v-else
                            class="display-item"
                            style="width: 100%; padding-top: 5px;"
                            :label="$t('order.order_details.show.information_component_management.order_detail_system_items.created_at')"
                            placeholder="yyyy/mm/dd"
                            :dateValue="importedAt | formatDateYmdHm(true)"
                            :isActive="false"
                        ></date-picker-without-buttons>
                    </span>
                </div>
            </div>
            <div>
                <div class="each-item-content">
                    <v-select
                        v-model="selectedSystemStatusId"
                        :label="$t('order.order_details.show.information_component_management.order_detail_system_items.system_status')"
                        :items="systemStatusSelectionCandidate"
                        style="max-width: 650px;"
                        dense
                        :disabled="!isEditMode"
                    >
                        <template slot="prepend">
                            <v-icon class="info-icon-size">mdi-wrench</v-icon>
                        </template>
                    </v-select>
                </div>
            </div>
            <div>
                <div class="my-2">
                    {{ $t('order.order_details.show.information_component_management.order_detail_info.custom_status') }}
                </div>
                <template v-for="customStatus in updatedCustomStatuses">
                    <div :key="customStatus.customStatusId" class="each-item-content">
                        <v-select
                            v-model="customStatus.selectAttributeId"
                            :label="customStatus.customStatusName"
                            :items="customStatus.attributes"
                            style="max-width: 650px;"
                            dense
                            :disabled="!isEditMode"
                        >
                            <template slot="prepend">
                                <v-tooltip top>
                                    <v-icon class="info-icon-size" slot="activator">mdi-wrench</v-icon>
                                    <span>{{ customStatus.customStatusName }}</span>
                                </v-tooltip>
                            </template>
                        </v-select>
                    </div>
                </template>
            </div>
            <div style="text-align: center;">
                <v-btn
                    v-if="isEditMode"
                    color="grey"
                    dark
                    @click="cancel"
                >{{ $t('common.button.cancel') }}</v-btn>
                <v-btn
                    color="primary"
                    v-if="isEditMode"
                    :disabled="disabledButton"
                    @click="save()"
                >{{ $t('common.button.save') }}</v-btn>
            </div>
        </div>
        <confirm-dialog ref="confirm"></confirm-dialog>
        <alert-dialog ref="alert"></alert-dialog>
    </div>
</template>

<script>
// Components
import DatePickerWithoutButtons from '../../../Atoms/Pickers/DatePickerWithoutButtons'
import ConfirmDialog from '../../../Atoms/Dialogs/ConfirmDialog'
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'
import CommonHeader from '../../../Molecules/Order/OrderDetails/CommonHeader'

// Mixins
import circleComponentMixin from '../../../../mixins/Order/OrderDetail/circleComponentMixin'

// Stores
import store from '../../../../stores/Order/OrderDetails/Show/store'

export default {
    mixins: [
        circleComponentMixin,
    ],
    components: {
        DatePickerWithoutButtons,
        ConfirmDialog,
        AlertDialog,
        CommonHeader,
    },
    data() {
        return {
            rules: {
                check: v => v.length <= 256 || Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.text_data_entry_rule.error_message.limit_order_detail_name', { number: 256 }),
                required: v => v !== '' || Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.text_data_entry_rule.error_message.no_order_detail_name')
            },
            valid: false,
            displayLangCode: 'ja', // 各案件固有情報を多言語対応するか分からないので、日本語に固定
            updatedSubjectData: '',
            updatedCustomStatuses: [],
            updatedOrderDetailData: []
        }
    },
    computed: {
        orderDetailId() {
            return store.state.processingData.orderDetailId
        },
        subject() {
            return store.state.processingData.subjectData
        },
        customStatuses() {
            return store.state.processingData.customStatuses
        },
        disabledButton() {
            return !this.valid
        },
        hideOtherButton() {
            return this.orderDetailId === 0
        },
        hideEditButton() {
            return this.orderDetailId === 0
        },
        orderDetailData() {
            return store.state.processingData.orderDetailData
        },
        selectedSystemStatusId: {
            set(selectedSystemStatusId) {
                this.updatedOrderDetailData.order_detail_is_active = selectedSystemStatusId
            },
            get() {
                return this.updatedOrderDetailData.order_detail_is_active
            }
        },
        importedAt() {
            return this.updatedOrderDetailData.created_at
        },
        systemStatusSelectionCandidate() {
            return [
                { text: this.$t('order.order_details.search_condition.status.active'), value: _const.FLG.ACTIVE },
                { text: this.$t('order.order_details.search_condition.status.archive'), value: _const.FLG.INACTIVE },
            ]
        },
    },
    watch: {
        subject: {
            immediate: true,
            async handler() {
                this.updatedSubjectData = store.state.processingData.subjectData
                await this.$nextTick()
                this.$refs.form.validate()
            },
        },
        customStatuses() {
            this.updatedCustomStatuses = JSON.parse(JSON.stringify(this.customStatuses))
        },
        orderDetailData() {
            this.updatedOrderDetailData = JSON.parse(JSON.stringify(this.orderDetailData))
        }
    },
    methods: {
        save: async function() {
            let message = ''
            if (this.orderDetailData.order_is_active === _const.FLG.INACTIVE) message += Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.is_order_inactive') + '<br>'
            message += Vue.i18n.translate('order.order_details.show.information_component_management.order_detail_info.save')

            if (await this.$refs.confirm.show(message)) {
                try {
                    await store.dispatch('saveOrderDetailSystemItems', {
                        orderDetailId: this.orderDetailId,
                        subjectData: this.updatedSubjectData,
                        orderDetailData: this.updatedOrderDetailData,
                        customStatuses: this.updatedCustomStatuses
                    })
                    // ダイアログ表示
                    this.$refs.alert.show(Vue.i18n.translate('common.message.saved'), () => {
                        this.toReadMode()
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
            this.updatedOrderDetailData = JSON.parse(JSON.stringify(this.orderDetailData))
            this.toReadMode()
        },
        showCounterIfEditing: function(count = true) {
            if (count === true) return true
            return this.isEditMode ? count : false
        },
    }
}
</script>

<style scoped>
</style>

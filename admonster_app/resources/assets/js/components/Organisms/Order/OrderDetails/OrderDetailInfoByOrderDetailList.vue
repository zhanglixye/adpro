<template>
    <div class="pt-3">
        <template v-for="header in shownHeaders">
            <div :key="header.value" v-if="isColumn(header.value) && columns !== undefined">
                <span
                    class="item-max-width"
                    style="display: flex; padding-top: 15px; padding-bottom: 15px;"
                    v-if="getColumn(header.value).item_type === dateItemType"
                >
                    <v-tooltip top>
                        <v-icon
                            class="info-icon-size mt-1"
                            slot="activator"
                        >mdi-file-excel</v-icon>
                        <span>{{ getDisplayName(getColumn(header.value).label_id) }}</span>
                    </v-tooltip>
                    <date-picker-without-buttons
                        class="display-item"
                        style="width: 100%;"
                        :label="getDisplayName(getColumn(header.value).label_id)"
                        placeholder="yyyy/mm/dd"
                        :dateValue="getColumn(header.value).value"
                        :isActive="isEditMode"
                        @change="updateColumnValue(deleteColumnConfigsPrefix(header.value), $event)"
                    ></date-picker-without-buttons>
                </span>
                <span v-if="getColumn(header.value).item_type === numItemType" style="width: 100%;">
                    <!-- @submit.prevent：enter押下によるページリロードを防ぐ -->
                    <v-form
                        :value="displayItemValid"
                        @input="$emit('update:displayItemValid', $event)"
                        @submit.prevent
                    >
                        <v-text-field
                            hint="999,999,999,999 ~ -999,999,999,999"
                            :rules="[rules.numCheck]"
                            :value="getColumn(header.value).value"
                            @input="updateColumnValue(deleteColumnConfigsPrefix(header.value), $event)"
                            :label="getDisplayName(getColumn(header.value).label_id)"
                            :disabled="!isEditMode"
                            class="item-max-width"
                            style="padding-top: 15px;"
                        >
                            <template slot="prepend">
                                <v-tooltip top>
                                    <v-icon class="info-icon-size" slot="activator">mdi-file-excel</v-icon>
                                    <span>{{ getDisplayName(getColumn(header.value).label_id) }}</span>
                                </v-tooltip>
                            </template>
                        </v-text-field>
                    </v-form>
                </span>
                <span
                    v-if="getColumn(header.value).item_type !== numItemType && getColumn(header.value).item_type !== dateItemType"
                    style="width: 100%;"
                >
                    <!-- @submit.prevent：enter押下によるページリロードを防ぐ -->
                    <v-form
                        :value="displayItemValid"
                        @input="$emit('update:displayItemValid', $event)"
                    >
                        <v-textarea
                            :rules="[rules.textMaxCheck]"
                            :value="getColumn(header.value).value"
                            @input="updateColumnValue(deleteColumnConfigsPrefix(header.value), $event)"
                            :label="getDisplayName(getColumn(header.value).label_id)"
                            class="item-max-width"
                            style="padding-top: 10px;"
                            :counter="showCounterIfEditing(2000)"
                            rows="1"
                            auto-grow
                            :disabled="!isEditMode"
                        >
                            <template slot="prepend">
                                <v-tooltip top>
                                    <v-icon class="info-icon-size" slot="activator">mdi-file-excel</v-icon>
                                    <span>{{ getDisplayName(getColumn(header.value).label_id) }}</span>
                                </v-tooltip>
                            </template>
                        </v-textarea>
                    </v-form>
                </span>
            </div>
            <!-- カスタムステータス -->
            <div :key="header.value" v-if="isCustomStatus(header.value) && updatedCustomStatuses.length !== 0">
                <div>
                    <v-select
                        :value="getCustomStatus(header.value).selectAttributeId"
                        @input="updateSelectAttributeId(getCustomStatus(header.value).customStatusId, $event)"
                        :label="getCustomStatus(header.value).customStatusName"
                        :items="getCustomStatus(header.value).attributes"
                        class="item-max-width"
                        dense
                        :disabled="!isEditMode"
                    >
                        <template slot="prepend">
                            <v-tooltip top>
                                <v-icon class="info-icon-size" slot="activator">mdi-wrench</v-icon>
                                <span>{{ getCustomStatus(header.value).customStatusName }}</span>
                            </v-tooltip>
                        </template>
                    </v-select>
                </div>
            </div>
            <!-- カスタムステータス -->
            <!-- システムステータス -->
            <div :key="header.value"  v-if="'status' === header.value">
                <v-select
                    v-model="selectedSystemStatusId"
                    :label="$t('order.order_details.show.information_component_management.order_detail_system_items.system_status')"
                    :items="systemStatusSelectionCandidate"
                    class="item-max-width"
                    dense
                    :disabled="!isEditMode"
                >
                    <template slot="prepend">
                        <v-icon class="info-icon-size">mdi-wrench</v-icon>
                    </template>
                </v-select>
            </div>
            <!-- システムステータス -->
            <!-- 発生日 -->
            <div :key="header.value" v-if="'created_at' === header.value">
                <span
                    class="item-max-width"
                    style="display: flex; padding-top: 15px; padding-bottom: 15px;"
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
            <!-- 発生日 -->
            <!-- 件名 -->
            <div :key="header.value" v-if="'order_detail_name' === header.value" class="d-flex item-max-width">
                <v-form
                    :value="valid"
                    @input="$emit('update:valid', $event)"
                    ref="form"
                    @submit.prevent
                >
                    <v-text-field
                        :class="isEditMode ? 'pr-3' : ''"
                        :value="updatedSubjectData"
                        @input="$emit('update:updatedSubjectData', $event)"
                        :label="$t('order.order_details.show.information_component_management.order_detail_info.subject')"
                        :counter="showCounterIfEditing(256)"
                        :rules="[rules.check, rules.required]"
                        :disabled="!isEditMode"
                    >
                        <template slot="prepend">
                            <v-icon class="info-icon-size">mdi-wrench</v-icon>
                        </template>
                    </v-text-field>
                </v-form>
                <v-checkbox
                    color="primary"
                    :input-value="isSubjectAutogeneration"
                    class="pt-3"
                    style="max-width: 130px;"
                    v-if="isEditMode"
                    @change="switchSubjectAutogeneration"
                    :label="$t('order.order_details.show.information_component_management.order_detail_info.do_autogeneration')"
                ></v-checkbox>
            </div>
            <!-- 件名 -->
        </template>
        <div style="text-align: center;" class="pt-3">
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
</template>

<script>
// Components
import DatePickerWithoutButtons from '../../../Atoms/Pickers/DatePickerWithoutButtons'
import OrderDetailInfo from './OrderDetailInfo'

// Stores
import store from '../../../../stores/Order/OrderDetails/Show/store'
import orderDetailListStore from '../../../../stores/Order/OrderDetails/store'

export default {
    components: {
        DatePickerWithoutButtons,
    },
    props: {
        isEditMode: { type: Boolean, required: true },
        updatedSubjectData: { type: String, required: true },
        updatedCustomStatuses: { type: Array, required: true },
        updatedOrderDetail: { type: Object, required: true },
        updateSelectAttributeId: { type: Function, required: true },
        updateColumnValue: { type: Function, required: true },
        valid: { type: Boolean, required: true },
        displayItemValid: { type: Boolean, required: true },
        isSubjectAutogeneration: { type: Boolean, required: true },
    },
    data() {
        return {
            rules: OrderDetailInfo.data().rules,
            displayLangCode: 'ja', // 各案件固有情報を多言語対応するか分からないので、日本語に固定
        }
    },
    computed: {
        columns() {
            return this.updatedOrderDetail['columns']
        },
        numItemType() {
            return _const.ITEM_TYPE.NUM.ID
        },
        dateItemType() {
            return _const.ITEM_TYPE.DATE.ID
        },
        labelData() {
            return store.state.processingData.labelData
        },
        disabledButton() {
            return !this.valid || !this.displayItemValid
        },
        shownHeaders() {
            return JSON.parse(localStorage.getItem('initialOrderDetails'))['eachOrderHeader'][this.updatedOrderDetail.order_id] !== undefined ?
                JSON.parse(localStorage.getItem('initialOrderDetails'))['eachOrderHeader'][this.updatedOrderDetail.order_id]['showHeaders'] : []
        },
        selectedSystemStatusId: {
            set(selectedSystemStatusId) {
                this.$emit('update-order-detail-is-active', selectedSystemStatusId)
            },
            get() {
                return this.updatedOrderDetail.order_detail_is_active
            }
        },
        systemStatusSelectionCandidate() {
            return [
                { text: this.$t('order.order_details.search_condition.status.active'), value: _const.FLG.ACTIVE },
                { text: this.$t('order.order_details.search_condition.status.archive'), value: _const.FLG.INACTIVE },
            ]
        },
        importedAt() {
            return this.updatedOrderDetail.created_at
        },
    },
    watch: {
        shownHeaders: {
            immediate: true,
            async handler() {
                await this.$nextTick()
                if (this.$refs.form !== undefined) this.$refs.form[0].validate()
            },
        },
        selectedSystemStatusId() {
            if (this.selectedSystemStatusId === '') this.selectedSystemStatusId = _const.FLG.ACTIVE
        }
    },
    methods: {
        getDisplayName: function(labelId) {
            return this.labelData[this.displayLangCode][labelId]
        },
        save: async function() {
            await this.$nextTick()
            this.$emit('save')
        },
        cancel: async function() {
            await this.$nextTick()
            this.$emit('cancel')
        },
        showCounterIfEditing: function(count = true) {
            if (count === true) return true
            return this.isEditMode ? count : false
        },
        isColumn(value) {
            return value.startsWith(orderDetailListStore.state.orderFileImportColumnConfigPrefix)
        },
        deleteColumnConfigsPrefix(value) {
            return value.replace(orderDetailListStore.state.orderFileImportColumnConfigPrefix, '')
        },
        getColumn(value) {
            return this.columns[this.deleteColumnConfigsPrefix(value)]
        },
        isCustomStatus(value) {
            return value.startsWith(orderDetailListStore.state.customStatusColumnPrefix)
        },
        getCustomStatus(value) {
            return this.updatedCustomStatuses.find(
                customStatus => customStatus.customStatusId === Number(value.replace(orderDetailListStore.state.customStatusColumnPrefix, ''))
            )
        },
        switchSubjectAutogeneration: async function(switchSubjectAutogeneration) {
            this.$emit('switch-subject-autogeneration', switchSubjectAutogeneration)
        },
    }
}
</script>

<style scoped>
.item-max-width {
    max-width: 650px;
}
</style>

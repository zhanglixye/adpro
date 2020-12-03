<template>
    <div>
        <div style="display: flex; margin-bottom: 10px;">
            <span style="padding-right: 15px;" class="pt-3">
                {{ $t('order.order_details.show.information_component_management.order_detail_info.subject') }}
            </span>
            <!-- @submit.prevent：enter押下によるページリロードを防ぐ -->
            <div style="width: 100%;">
                <v-form
                    :value="valid"
                    @input="$emit('update:valid', $event)"
                    @submit.prevent
                    ref="form"
                >
                    <v-text-field
                        :class="isEditMode ? 'pr-3' : ''"
                        :value="updatedSubjectData"
                        @input="$emit('update:updatedSubjectData', $event)"
                        label
                        :style="isEditMode ? 'padding-top: 5px;' : 'padding-top: 5px; padding-right: 60px;'"
                        :counter="showCounterIfEditing(256)"
                        :rules="[rules.check, rules.required]"
                        :disabled="!isEditMode"
                    ></v-text-field>
                </v-form>
            </div>
            <v-checkbox
                color="primary"
                :input-value="isSubjectAutogeneration"
                class="pt-3"
                v-if="isEditMode"
                @change="switchSubjectAutogeneration"
                :label="$t('order.order_details.show.information_component_management.order_detail_info.do_autogeneration')"
            ></v-checkbox>
        </div>
        <div>
            <div class="my-2" style="max-width: 665px;">
                <span>{{ $t('order.order_details.show.information_component_management.order_detail_info.item') }}</span>
            </div>
            <template
                v-for="(column, i) in columns"
            >
                <div
                    :key="column.label_id"
                    class="each-item-content"
                >
                    <span
                        style="display: flex; padding-top: 15px; padding-bottom: 15px;"
                        v-if="column.item_type === dateItemType"
                        class="item-max-width"
                    >
                        <v-tooltip top>
                            <v-icon
                                class="info-icon-size mt-1"
                                slot="activator"
                            >mdi-file-excel</v-icon>
                            <span>{{ getDisplayName(column.label_id) }}</span>
                        </v-tooltip>
                        <date-picker-without-buttons
                            class="display-item"
                            style="width: 100%;"
                            :label="getDisplayName(column.label_id)"
                            placeholder="yyyy/mm/dd"
                            :dateValue="column.value"
                            :isActive="isEditMode"
                            @change="updateColumnValue(i, $event)"
                        ></date-picker-without-buttons>
                    </span>
                    <span v-if="column.item_type === numItemType" style="width: 100%;">
                        <!-- @submit.prevent：enter押下によるページリロードを防ぐ -->
                        <v-form
                            :value="displayItemValid"
                            @input="$emit('update:displayItemValid', $event)"
                            @submit.prevent
                        >
                            <v-text-field
                                hint="999,999,999,999 ~ -999,999,999,999"
                                :rules="[rules.numCheck]"
                                :value="column.value"
                                @input="updateColumnValue(i, $event)"
                                :label="getDisplayName(column.label_id)"
                                :disabled="!isEditMode"
                                style="padding-top: 15px;"
                                class="item-max-width"
                            >
                                <template slot="prepend">
                                    <v-tooltip top>
                                        <v-icon class="info-icon-size" slot="activator">mdi-file-excel</v-icon>
                                        <span>{{ getDisplayName(column.label_id) }}</span>
                                    </v-tooltip>
                                </template>
                            </v-text-field>
                        </v-form>
                    </span>
                    <span v-if="column.item_type !== numItemType && column.item_type !== dateItemType" style="width: 100%;">
                        <!-- @submit.prevent：enter押下によるページリロードを防ぐ -->
                        <v-form
                            :value="displayItemValid"
                            @input="$emit('update:displayItemValid', $event)"
                            @submit.prevent
                        >
                            <v-textarea
                                :rules="[rules.textMaxCheck]"
                                :value="column.value"
                                @input="updateColumnValue(i, $event)"
                                :label="getDisplayName(column.label_id)"
                                style="padding-top: 10px;"
                                class="item-max-width"
                                :counter="showCounterIfEditing(2000)"
                                rows="1"
                                auto-grow
                                :disabled="!isEditMode"
                            >
                                <template slot="prepend">
                                    <v-tooltip top>
                                        <v-icon class="info-icon-size" slot="activator">mdi-file-excel</v-icon>
                                        <span>{{ getDisplayName(column.label_id) }}</span>
                                    </v-tooltip>
                                </template>
                            </v-textarea>
                        </v-form>
                    </span>
                </div>
            </template>
        </div>
        <div>
            <div class="my-2">
                {{ $t('order.order_details.show.information_component_management.order_detail_info.custom_status') }}
            </div>
            <template v-for="customStatus in updatedCustomStatuses">
                <div :key="customStatus.customStatusId" class="each-item-content">
                    <v-select
                        :value="customStatus.selectAttributeId"
                        @input="updateSelectAttributeId(customStatus.customStatusId, $event)"
                        :label="customStatus.customStatusName"
                        :items="customStatus.attributes"
                        class="item-max-width"
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
</template>

<script>
// Components
import DatePickerWithoutButtons from '../../../Atoms/Pickers/DatePickerWithoutButtons'
import OrderDetailInfo from './OrderDetailInfo'

// Stores
import store from '../../../../stores/Order/OrderDetails/Show/store'

export default {
    components: {
        DatePickerWithoutButtons
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
    },
    watch: {
        updatedSubjectData: {
            immediate: true,
            async handler() {
                await this.$nextTick()
                this.$refs.form.validate()
            },
        },
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
            this.$emit('cancel')
        },
        showCounterIfEditing: function(count = true) {
            if (count === true) return true
            return this.isEditMode ? count : false
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

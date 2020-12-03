<template>
    <v-dialog v-model="dialog" persistent width="700">
        <v-card>
            <v-card-title class="pb-0">
                <span class="headline">
                    {{ $t('order.order_details.dialog.bulk_update_order_detail.title') }}
                </span>
            </v-card-title>
            <v-card-text>
                <div
                    :style="{
                        'overflow': 'auto',
                        'padding-left': '16px',
                        'padding-bottom': '16px',
                        'padding-right': '16px',
                        'white-space': 'nowrap',
                    }"
                >
                    <div>
                        <div class="my-2" style="max-width: 665px;">
                            <span>{{ $t('order.order_details.show.information_component_management.order_detail_info.item') }}</span>
                            <span style="display: inline-block; float: right;">{{ $t('order.order_details.dialog.bulk_update_order_detail.not_update') }}</span>
                        </div>
                        <template
                            v-for="column in columns"
                        >
                            <div
                                :key="column.label_id"
                                class="each-item-content"
                                style="display: flex;"
                            >
                                <span
                                    style="width: 650px; display: flex; padding-top: 15px; padding-bottom: 15px;"
                                    v-if="column.item_type === dateItemType"
                                >
                                    <v-tooltip top>
                                        <v-icon
                                            class="info-icon-size"
                                            style="margin-top: 4px;"
                                            slot="activator"
                                        >mdi-file-excel</v-icon>
                                        <span>{{ getDisplayName(column.label_id) }}</span>
                                    </v-tooltip>
                                    <date-picker-without-buttons
                                        class="display-item"
                                        style="width: 100%; padding-top: 5px;"
                                        :label="getDisplayName(column.label_id)"
                                        :dateValue="column.value"
                                        :isActive="!disabledItem(column.id)"
                                        placeholder="yyyy/mm/dd"
                                        @change="column.value = $event"
                                    ></date-picker-without-buttons>
                                </span>
                                <span v-if="column.item_type === numItemType" style="width: 100%;">
                                    <!-- @submit.prevent：enter押下によるページリロードを防ぐ -->
                                    <v-form v-model="displayItemValid" @submit.prevent>
                                        <v-text-field
                                            hint="999,999,999 ,999 ~ -999,999,999,999"
                                            :rules="[rules.numCheck]"
                                            v-model="column.value"
                                            :label="getDisplayName(column.label_id)"
                                            :disabled="disabledItem(column.id)"
                                            style="max-width: 650px; padding-top: 15px;"
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
                                    <v-form v-model="displayItemValid" @submit.prevent>
                                        <v-textarea
                                            :label="getDisplayName(column.label_id)"
                                            :rules="[rules.textMaxCheck]"
                                            v-model="column.value"
                                            style="max-width: 650px; padding-top: 10px;"
                                            counter="2000"
                                            rows="1"
                                            auto-grow
                                            :disabled="disabledItem(column.id)"
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
                                <span style="margin: 0 15px;">
                                    <v-checkbox
                                        :input-value="disabledItem(column.id)"
                                        primary
                                        hide-details
                                        color="primary"
                                        @click.native="selectedItem(column.id)"
                                    ></v-checkbox>
                                </span>
                            </div>
                        </template>
                    </div>
                    <div>
                        <div class="my-2">
                            {{ $t('order.order_details.show.information_component_management.order_detail_info.custom_status') }}
                        </div>
                        <template v-for="customStatus in customStatuses">
                            <div :key="customStatus.id" class="each-item-content">
                                <v-select
                                    v-model="customStatus.selectAttributeId"
                                    :label="customStatus.text"
                                    :items="customStatus.attributes"
                                    style="max-width: 650px;"
                                    @change="changeNotUpdateCustomStatuses()"
                                    dense
                                >
                                    <template slot="prepend">
                                        <v-tooltip top>
                                            <v-icon class="info-icon-size" slot="activator">mdi-wrench</v-icon>
                                            <span>{{ customStatus.text }}</span>
                                        </v-tooltip>
                                    </template>
                                </v-select>
                            </div>
                        </template>
                    </div>
                </div>
            </v-card-text>
            <div class="btn-center-block">
                <v-btn color="grey" dark @click="cancel()">{{ $t('common.button.cancel') }}</v-btn>
                <v-btn color="primary" :disabled="disabledButton" @click="next()">{{ $t('common.button.save') }}</v-btn>
            </div>
        </v-card>
    </v-dialog>
</template>

<script>
import DatePickerWithoutButtons from '../../../Atoms/Pickers/DatePickerWithoutButtons'
import OrderDetailInfo from './OrderDetailInfo'
import store from '../../../../stores/Order/OrderDetails/store'

export default {
    components: {
        DatePickerWithoutButtons,
    },
    data() {
        return {
            dialog: false,
            resolve: null,
            reject: null,
            notUpdateCustomId: -1, // カスタムステータスを変更しないようにするID
            displayLangCode: 'ja',
            rules: OrderDetailInfo.data().rules,
            displayItemValid: false,
            notUpdateColumnIds: [],
            notUpdateCustomStatuses: [],
        }
    },
    computed: {
        columns() {
            const columns = store.state.processingData.orderFileImportColumnConfigs
            return columns.map(column => ({
                id: column.id,
                value: '',
                label_id: column.label_id,
                item_type: column.item_type,
            }))
        },
        customStatuses() {
            const customStatuses = []
            const notUpdateCustomAttribute = {
                text: this.$t('order.order_details.dialog.bulk_update_order_detail.not_update'),
                value: this.notUpdateCustomId
            }
            store.state.processingData.customStatuses.forEach(customStatus => {
                const customStatusAttributes = []
                customStatusAttributes.push(notUpdateCustomAttribute)
                customStatus.attributes.forEach(attribute => {
                    customStatusAttributes.push({
                        id: attribute.id,
                        text: attribute.text,
                        value: attribute.id,
                    })
                })
                customStatuses.push({
                    id: customStatus.id,
                    selectAttributeId: null,
                    text: customStatus.text,
                    attributes: customStatusAttributes
                })
            })
            return customStatuses
        },
        labelData() {
            return store.state.processingData.labelData
        },
        startedAt () {
            return store.state.processingData.startedAt
        },
        numItemType() {
            return _const.ITEM_TYPE.NUM.ID
        },
        dateItemType() {
            return _const.ITEM_TYPE.DATE.ID
        },
        disabledButton() {
            return !this.displayItemValid || (this.columns.length === this.notUpdateColumnIds.length && this.customStatuses.length === this.notUpdateCustomStatuses.length)
        },
    },
    methods: {
        toTop() {
            const dialogElements = document.getElementsByClassName('v-dialog--active')
            if (!dialogElements || !dialogElements.length) {
                return
            }
            const scrolled = dialogElements[0].scrollTop
            if (scrolled > 0) {
                dialogElements[0].scrollTop = 0
            }
        },
        getDisplayName(labelId) {
            return this.labelData[this.displayLangCode][labelId]
        },
        show() {
            window.setTimeout(this.toTop, 10)
            this.dialog = true
            this.notUpdateColumnIds = []
            this.columns.forEach(column => {
                column['value'] = ''
                this.notUpdateColumnIds.push(column.id)
            })
            this.customStatuses.forEach(customStatus => customStatus.selectAttributeId = this.notUpdateCustomId)
            this.notUpdateCustomStatuses = this.customStatuses
            return new Promise((resolve, reject) => {
                this.resolve = resolve
                this.reject = reject
            })
        },
        cancel() {
            this.resolve(null)
            this.dialog = false
        },
        next() {
            let updateColumns = this.columns
            this.notUpdateColumnIds.forEach(notUpdateColumnId => {
                updateColumns = updateColumns.filter(column => column.id !== notUpdateColumnId)
            })
            const updateCustomStatuses = this.customStatuses.filter(customStatus => customStatus.selectAttributeId !== this.notUpdateCustomId)
            const updateData = {
                columns: updateColumns,
                customStatuses: updateCustomStatuses,
                startedAt: this.startedAt.date,
            }
            this.resolve(updateData)
            this.dialog = false
        },
        selectedItem(columnId) {
            if (!this.notUpdateColumnIds.includes(columnId)) {
                // add
                this.notUpdateColumnIds.push(columnId)
            } else {
                // remove
                this.notUpdateColumnIds = this.notUpdateColumnIds.filter(
                    id => id !== columnId
                )
            }
        },
        disabledItem(columnId) {
            return this.notUpdateColumnIds.includes(columnId)
        },
        changeNotUpdateCustomStatuses() {
            this.notUpdateCustomStatuses = this.customStatuses.filter(customStatus => customStatus.selectAttributeId === this.notUpdateCustomId)
        },
    }
}
</script>

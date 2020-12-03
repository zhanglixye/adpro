<template>
    <v-dialog v-model="dialog" persistent width="500">
        <v-card>
            <v-card-title class="pb-2">
                <span class="text-color headline">
                    {{ $t('imported_files.order_imported_setting.date_data_entry_rule.subject') }}
                </span>
                <v-select
                    item-value="value"
                    item-text="label"
                    :items="dates"
                    v-model="selected"
                    hide-details
                    single-line
                    :disabled="readonly"
                    :style="{ 'max-width': '200px', 'padding-top': '0px', left: '220px' }"
                ></v-select>
            </v-card-title>
            <v-card-text style="text-align: left;">
                <div class="text-color">
                    {{ $t('imported_files.order_imported_setting.date_data_entry_rule.rule_preference') }}
                </div>
                <span class="indent-interval">
                    <date-picker-without-buttons
                        label="from"
                        :dateValue="date['from']"
                        from
                        :comparisonDate="date.to"
                        placeholder="yyyy/mm/dd"
                        @change="date.from = $event"
                        :isActive="!readonly && selected === 'custom' ? true : false"
                    ></date-picker-without-buttons>
                    <span :style="{ 'display': 'flex', 'justify-content': 'center', 'align-items': 'center' }">～</span>
                    <date-picker-without-buttons
                        label="to"
                        :dateValue="date.to"
                        :comparisonDate="date['from']"
                        to
                        placeholder="yyyy/mm/dd"
                        @change="date.to = $event"
                        :isActive="!readonly && selected === 'custom' ? true : false"
                    ></date-picker-without-buttons>
                </span>
                <span class="indent-interval">
                    <div class="text-color" style="margin-top: auto; margin-bottom: auto; margin-right: 10px">
                        {{ $t('imported_files.order_imported_setting.date_data_entry_rule.required') }}
                    </div>
                    <v-switch v-model="isInputRequired" :disabled="readonly" color="primary"></v-switch>
                </span>
                <span>
                    <div class="text-color">
                        {{ $t('imported_files.order_imported_setting.date_data_entry_rule.display_format') }}
                    </div>
                    <span class="indent-interval">
                        <v-radio-group
                            v-model="selectedAlignItem"
                            row
                        >
                            <v-radio
                                v-for="(item) in alignItems"
                                :key="item.id"
                                :label="item.text"
                                :value="item.id"
                                :disabled="readonly"
                            ></v-radio>
                        </v-radio-group>
                    </span>
                </span>
            </v-card-text>
            <div style="display: flex;">
                <v-spacer></v-spacer>
                <v-btn color="grey" dark @click="cancel()">{{ $t('common.button.cancel') }}</v-btn>
                <v-btn
                    v-if="!readonly"
                    color="primary"
                    @click="setting()"
                    :disabled="disabledButton"
                >
                    {{ $t('common.button.setting') }}
                </v-btn>
            </div>
        </v-card>
    </v-dialog>
</template>

<script>
import DatePickerWithoutButtons from '../../../Atoms/Pickers/DatePickerWithoutButtons'
export default {
    components: {
        DatePickerWithoutButtons
    },
    props: {
        readonly: { type: Boolean, required: false, default: false },
    },
    data: () => ({
        index: null,
        isInputRequired: false,
        beforeIsInputRequired: false,
        dialog: false,
        selected: 'custom',
        beforeSelected: 'custom',
        selectedAlignItem: _const.DISPLAY_FORMAT.COMMON_ALIGN_CENTER,
        beforeSelectedAlignItem: _const.DISPLAY_FORMAT.COMMON_ALIGN_CENTER,
        dates: [
            {
                label: Vue.i18n.translate('imported_files.order_imported_setting.date_data_entry_rule.custom'),
                value: 'custom',
            },
            {
                label: Vue.i18n.translate('imported_files.order_imported_setting.date_data_entry_rule.today'),
                value: 'today',
            },
            {
                label: Vue.i18n.translate('imported_files.order_imported_setting.date_data_entry_rule.yesterday'),
                value: 'yesterday',
            },
            {
                label: Vue.i18n.translate('imported_files.order_imported_setting.date_data_entry_rule.currentMonth'),
                value: 'currentMonth',
            },
            {
                label: Vue.i18n.translate('imported_files.order_imported_setting.date_data_entry_rule.lastMonth'),
                value: 'lastMonth',
            },
            {
                label: Vue.i18n.translate('imported_files.order_imported_setting.date_data_entry_rule.currentYear'),
                value: 'currentYear',
            },
        ],
        date: { 'from': '', 'to': '' },
        beforeDate: { 'from': '', 'to': '' }
    }),
    computed: {
        alignItems () {
            const alignItems = []
            for (const [alignItemKey, alignItemValue] of Object.entries(_const.DISPLAY_FORMAT)) {
                if (!alignItemKey.match(/COMMON_ALIGN/g)) continue
                alignItems.push(
                    {
                        'id': alignItemValue,
                        'text': this.$t(`order.order_details.dialog.setting_display_format.item_type_common.${_const.PREFIX}${alignItemValue}`)
                    }
                )
            }
            return alignItems
        },
        disabledButton () {
            return this.date.from !== '' && this.date.to !== '' && this.date.from > this.date.to
        },
        dataType () {
            return _const.ITEM_TYPE.DATE.ID
        },
        form () {
            const selectedValues = this.selectedAlignItem === null ? [] : [this.selectedAlignItem]
            return {
                selectType: this.dataType,
                inputRule: {
                    date: this.date,
                    selected: this.selected,
                    isInputRequired: this.isInputRequired,
                    selectType: this.dataType
                },
                displayRule: selectedValues
            }
        }
    },
    watch: {
        selected (val) {
            let now = new Date()
            let year = null
            let month = null
            let date = null
            switch (val) {
            case 'today':
                year = now.getFullYear()
                month = ('00' + (now.getMonth() + 1)).slice(-2)
                date = ('00' + now.getDate()).slice(-2)
                this.date = Object.assign({}, this.date, { from: year + '/' + month + '/' + date })
                this.date = Object.assign({}, this.date, { to: year + '/' + month + '/' + date })
                break
            case 'yesterday':
                now.setDate(now.getDate() - 1)
                year = now.getFullYear()
                month = ('00' + (now.getMonth() + 1)).slice(-2)
                date = ('00' + now.getDate()).slice(-2)
                this.date = Object.assign({}, this.date, { from: year + '/' + month + '/' + date })
                this.date = Object.assign({}, this.date, { to: year + '/' + month + '/' + date })
                break
            case 'currentMonth':
                now.setDate(1)
                year = now.getFullYear()
                month = ('00' + (now.getMonth() + 1)).slice(-2)
                date = ('00' + now.getDate()).slice(-2)
                this.date = Object.assign({}, this.date, { from: year + '/' + month + '/' + date })

                now = new Date(now.getFullYear(), now.getMonth() + 1, 0)
                year = now.getFullYear()
                month = ('00' + (now.getMonth() + 1)).slice(-2)
                date = ('00' + now.getDate()).slice(-2)
                this.date = Object.assign({}, this.date, { to: year + '/' + month + '/' + date })

                break
            case 'lastMonth':
                now = new Date(now.getFullYear(), now.getMonth(), 0)
                now.setDate(1)
                year = now.getFullYear()
                month = ('00' + (now.getMonth() + 1)).slice(-2)
                date = ('00' + now.getDate()).slice(-2)
                this.date = Object.assign({}, this.date, { from: year + '/' + month + '/' + date })

                now = new Date(now.getFullYear(), now.getMonth() + 1, 0)
                year = now.getFullYear()
                month = ('00' + (now.getMonth() + 1)).slice(-2)
                date = ('00' + now.getDate()).slice(-2)
                this.date = Object.assign({}, this.date, { to: year + '/' + month + '/' + date })
                break
            case 'currentYear':
                year = now.getFullYear()
                this.date = Object.assign({}, this.date, { from: year + '/01/01' })
                this.date = Object.assign({}, this.date, { to: year + '/12/31' })
                break
            default:
                break
            }
        },
    },
    methods: {
        async show (index, form) {

            // 既に設定されているデータを反映
            if (form !== undefined && form['selectType'] === this.dataType) {

                // 表示設定
                let displayRule = form['displayRule']
                if (displayRule.length > 0) {
                    this.alignItems.forEach(alignItem => {
                        if (displayRule.includes(alignItem.id)) {
                            this.selectedAlignItem = alignItem.id
                            displayRule = displayRule.filter(displayFormat => displayFormat !== alignItem.id)
                        }
                    })
                } else {
                    this.selectedAlignItem = null // alignを未選択にする
                }

                // ルール設定
                if (form['inputRule'] !== null && form['inputRule'].selectType === this.dataType) {
                    this.selected = form['inputRule'].selected
                    await this.$nextTick()
                    this.date = Object.assign({}, form['inputRule'].date)
                    this.isInputRequired = form['inputRule'].isInputRequired
                }
            }
            this.index = index
            this.beforeSelected = this.selected
            this.beforeDate = Object.assign({}, this.date)
            this.beforeIsInputRequired = this.isInputRequired
            this.beforeSelectedAlignItem = this.selectedAlignItem
            this.dialog = true
        },
        cancel () {
            this.date = Object.assign({}, this.beforeDate)
            this.selected = this.beforeSelected
            this.isInputRequired = this.beforeIsInputRequired
            this.selectedAlignItem = this.beforeSelectedAlignItem
            this.dialog = false
        },
        setting () {
            this.$emit('setting', this.form, this.index)
            this.dialog = false
        }
    }
}
</script>

<style scoped>
.text-color {
    color: rgba(0, 0, 0, 0.54);
    font-size: 14px;
    font-weight: 700;
}
.indent-interval {
    display: flex;
    margin-left: 32px;
}
</style>

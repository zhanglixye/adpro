<template>
    <v-dialog v-model="dialog" persistent width="500">
        <v-card>
            <v-card-title class="pb-2">
                <span class="text-color headline">
                    {{ $t('imported_files.order_imported_setting.url_data_entry_rule.subject') }}
                </span>
            </v-card-title>
            <v-card-text style="text-align: left;">
                <span>
                    <div style="margin-top: 15px;" class="text-color">
                        {{ $t('imported_files.order_imported_setting.url_data_entry_rule.display_format') }}
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
export default {
    props: {
        readonly: { type: Boolean, required: false, default: false },
    },
    data () {
        return {
            index: null,
            dialog: false,
            selectedAlignItem: _const.DISPLAY_FORMAT.COMMON_ALIGN_LEFT,
            beforeSelectedAlignItem: _const.DISPLAY_FORMAT.COMMON_ALIGN_LEFT,
        }
    },
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
            return this.selectedAlignItem === null
        },
        dataType () {
            return _const.ITEM_TYPE.URL.ID
        },
        form () {
            const selectedValues = this.selectedAlignItem === null ? [] : [this.selectedAlignItem]
            return {
                selectType: this.dataType,
                inputRule: {
                    selectType: this.dataType,
                },
                displayRule: selectedValues,
            }
        },
    },
    methods: {
        show (index, form) {
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
            }
            this.beforeSelectedAlignItem = this.selectedAlignItem
            this.index = index
            this.dialog = true
        },
        cancel () {
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

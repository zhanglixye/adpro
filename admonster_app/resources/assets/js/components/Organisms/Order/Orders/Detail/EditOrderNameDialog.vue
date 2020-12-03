<template>
    <v-dialog
        v-model="dialog"
        persistent
        width="800"
    >
        <v-card>
            <v-card-title
                class="headline pa-3"
                primary-title
                style="border-bottom: 1px solid #d7d7d7;"
            >
                <span style="font-size:20px;">{{ $t('order.orders.setting.edit_order_name') }}</span>
                <v-spacer></v-spacer>
                <v-btn flat icon small class="ma-0" @click="close()">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            <v-card-text class="pt-3 pr-4 pb-3 pl-4">
                <span style="font-size: 16px;">{{ $t('order.orders.setting.order_name') }}</span>
                <v-form v-model="displayItemValid" @submit.prevent>
                    <v-text-field
                        :rules="rules"
                        v-model="orderName"
                        clearable
                        :counter="maxLengthOrderName"
                        style="padding-left: 10px;"
                    ></v-text-field>
                </v-form>
            </v-card-text>
            <v-card-actions class="btn-center-block pt-4 pr-0 pb-4 pl-0">
                <v-btn
                    class="mr-3"
                    color="grey"
                    large
                    dark
                    text
                    @click="close()"
                    style="width: 120px;"
                >
                    {{ $t('common.button.cancel') }}
                </v-btn>
                <v-btn
                    color="primary"
                    large
                    text
                    :disabled="!displayItemValid"
                    @click="save()"
                    style="width: 120px;"
                >
                    {{ $t('common.button.save') }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import store from '../../../../../stores/Order/Orders/Detail/store'

export default {
    props: {
        maxLengthOrderName: { type: Number, required: false, default: 256 },
    },
    data: () => ({
        dialog: false,
        displayItemValid: false,
        updateOrderName: null,
    }),
    computed: {
        rules () {
            return [
                v => (v !== null && v.length !== 0) || Vue.i18n.translate('order.orders.setting.error.no_order_name'),// min
                v => (v !== null && v.length <= this.maxLengthOrderName) || Vue.i18n.translate('order.orders.setting.error.limit_order_name', {number: this.maxLengthOrderName})// max
            ]
        },
        orderName: {
            set(val) {
                if (val === null) val = ''
                this.updateOrderName = val
            },
            get() {
                return this.updateOrderName === null ? store.state.processingData.orderName : this.updateOrderName
            },
        },
    },
    methods: {
        show() {
            this.dialog = true
            this.updateOrderName = this.orderName
        },
        close() {
            this.dialog = false
            this.updateOrderName = null
        },
        save: async function() {
            this.dialog = false
            await store.dispatch('updateOrderName', this.updateOrderName)
            this.$emit('save')
        },
    },
}
</script>

<style scoped>
</style>

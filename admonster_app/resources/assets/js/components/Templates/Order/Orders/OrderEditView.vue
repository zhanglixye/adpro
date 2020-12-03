<template>
    <v-app>
        <app-menu :drawer="sideMenuDrawer"></app-menu>
        <app-header :title="$t('order.orders.setting.title')"></app-header>
        <v-content>
            <v-container fluid grid-list-md style="background-color: #f0f0f0;">
                <v-layout row wrap>
                    <v-flex xs12>
                        <page-header back-button></page-header>
                    </v-flex>
                    <v-flex xs12>
                        <settings
                            :orderId="inputs.order_id"
                            @save="save"
                        ></settings>
                        <order-operator :orderId="inputs.order_id" class="mt-0 mr-2 mb-0 ml-2"></order-operator>
                    </v-flex>

                    <v-flex xs12>
                        <page-footer back-button></page-footer>
                    </v-flex>
                </v-layout>
            </v-container>
            <progress-circular v-if="loading"></progress-circular>
            <confirm-dialog ref="confirm"></confirm-dialog>
            <alert-dialog ref="alert"></alert-dialog>
        </v-content>
        <app-footer></app-footer>
    </v-app>
</template>

<script>
import PageHeader from '../../../Organisms/Layouts/PageHeader'
import ProgressCircular from '../../../Atoms/Progress/ProgressCircular'
import Settings from '../../../Organisms/Order/Orders/Detail/Settings'
import OrderOperator from '../../../Organisms/Order/Orders/Detail/OrderOperator'
import ConfirmDialog from '../../../Atoms/Dialogs/ConfirmDialog'
import AlertDialog from '../../../Atoms/Dialogs/AlertDialog'
import PageFooter from '../../../Organisms/Layouts/PageFooter'
import store from '../../../../stores/Order/Orders/Detail/store'

export default {
    components: {
        PageHeader,
        ProgressCircular,
        Settings,
        OrderOperator,
        ConfirmDialog,
        AlertDialog,
        PageFooter,
    },
    props: {
        inputs: { type: Object, required: true },
    },
    data: () => ({
        loading: false,
        initData: null,
        sideMenuDrawer: false,
        orderCreateUserFlg: false,

    }),
    computed: {
        isDisabled: function() {
            const orderName = store.state.processingData.orderName
            return orderName.length < 1 || 256 < orderName.length
        },
        order: function() {
            return store.state.order
        }
    },
    mounted() {
        this.load()
    },
    methods: {
        save: async function() {
            try {
                this.loading = true
                await store.dispatch('tryUpdate')
                await store.dispatch('tryLoad', this.inputs.order_id)
                this.loading = false
            } catch (error) {
                this.loading = false
                this.$refs.alert.show(error)
            }
        },
        load: async function() {
            let func = () => {window.location.href = `/order/order_details?order_id=${this.inputs.order_id}`}
            if (window.history.length > 1) {
                func = () => {window.history.back()}
            }

            try {
                this.loading = true
                await store.dispatch('tryLoad', this.inputs.order_id)
            } catch (error) {
                if (error === 'no_admin_permission') {
                    this.$refs.alert.show(this.$t('common.message.no_admin_permission'), func)
                } else {
                    this.$refs.alert.show(this.$t('common.message.internal_error'), func)
                }
            }
            this.loading = false
        },
    },
}
</script>

<style scoped>
</style>

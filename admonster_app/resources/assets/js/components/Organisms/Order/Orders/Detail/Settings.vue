<template>
    <div id="settings">
        <div class="setting-wrap mt-0 mr-2 mb-3 ml-2">
            <v-container fluid class="pt-2 pr-2 pb-2 pl-3" style="border-bottom: 1px solid #d7d7d7;">
                <v-layout row wrap align-center>
                    <span style="font-size: 16px;">{{ $t('order.orders.setting.order_name') }}</span>
                    <v-spacer></v-spacer>
                    <v-tooltip top>
                        <v-btn
                            flat
                            icon
                            color="primary"
                            slot="activator"
                            @click="showEditOrderNameDialog()"
                        >
                            <v-icon>mdi-pencil</v-icon>
                        </v-btn>
                        <span>{{ $t('order.orders.setting.edit_order_name') }}</span>
                    </v-tooltip>
                </v-layout>
            </v-container>
            <div class="pt-2 pr-4 pb-3 pl-4">
                <v-text-field
                    v-model="orderName"
                    readonly
                ></v-text-field>
            </div>
        </div>
        <div class="setting-wrap mt-0 mr-2 mb-3 ml-2">
            <v-container fluid class="pt-2 pr-2 pb-2 pl-3">
                <v-layout row wrap align-center>
                    <span style="font-size: 16px;">{{ $t('order.orders.setting.link_edit_item_name') }}</span>
                    <v-spacer></v-spacer>
                    <v-tooltip top>
                        <v-btn
                            flat
                            icon
                            color="primary"
                            slot="activator"
                            @click="openOrderItemEdit()"
                        >
                            <v-icon>mdi-arrow-right</v-icon>
                        </v-btn>
                        <span>{{ $t('order.orders.setting.link_edit_item_name_tooltip') }}</span>
                    </v-tooltip>
                </v-layout>
            </v-container>
        </div>
        <div class="setting-wrap mt-0 mr-2 mb-3 ml-2">
            <custom-status-setting
                :orderId="orderId"
                @save="save"
            ></custom-status-setting>
        </div>
        <edit-order-name-dialog
            ref="editOrderNameDialog"
            :maxLengthOrderName="maxLengthOrderName"
            @save="save"
        ></edit-order-name-dialog>
    </div>
</template>

<script>
import CustomStatusSetting from './CustomStatusSetting'
import EditOrderNameDialog from './EditOrderNameDialog'
import store from '../../../../../stores/Order/Orders/Detail/store'

export default {
    components: {
        CustomStatusSetting,
        EditOrderNameDialog
    },
    props: {
        orderId: { type: String, required: true },
    },
    data: () => ({
        maxLengthOrderName: 256,
    }),
    computed: {
        orderName () {
            return store.state.processingData.orderName
        },
    },
    methods: {
        openOrderItemEdit () {
            window.location.href = '/order/orders/' + this.orderId + '/item/edit/'
        },
        showEditOrderNameDialog: function () {
            this.$refs.editOrderNameDialog.show()
        },
        save: async function() {
            this.$emit('save')
        },
    },
};
</script>

<style scoped>
.setting-wrap {
    background-color: #ffffff;
}
.setting-wrap-header:hover {
    text-decoration: underline;
    cursor: pointer;
}
.setting-wrap-header .setting-wrap-header-icon {
    display: block;
}
.setting-wrap-header .setting-wrap-header-icon:hover {
    text-decoration: none;
}
#business-overview {
    background-color: #ffffff;
}
#business-overview-main {
    height: 350px;
}
</style>

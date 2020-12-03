<template>
    <div
        id="custom-status-setting"
    >
        <div>
            <v-container fluid class="pt-2 pr-2 pb-2 pl-3" :class="customStatuses.length > 0 ? 'header-bottom-border' : ''">
                <v-layout row wrap align-center>
                    <span style="font-size: 16px;">{{ $t('order.orders.setting.custom_status.title') }}</span>
                    <v-spacer></v-spacer>
                    <v-tooltip top>
                        <v-btn
                            flat
                            icon
                            color="primary"
                            slot="activator"
                            @click="openCustomStatusModal()"
                        >
                            <v-icon>mdi-plus-circle</v-icon>
                        </v-btn>
                        <span>{{ $t('order.orders.setting.custom_status.add') }}</span>
                    </v-tooltip>
                </v-layout>
            </v-container>
        </div>
        <div :class="customStatuses.length > 0 ? 'pb-2' : ''">
            <draggable
                :list="customStatuses"
                :options="{ animation: 300, handle: '.handle' }"
                @end="onEnd"
            >
                <li
                    class="list-group-item"
                    style="overflow: auto; margin:15px; border-radius: unset; border: 1px solid #f2f2f2"
                    v-for="customStatus in customStatuses"
                    :key="customStatus.forKey"
                >
                    <v-container fluid class="pa-0">
                        <v-layout row wrap align-center>
                            <span class="handle">
                                <v-icon>mdi-menu</v-icon>
                            </span>
                            <v-tooltip top>
                                <span
                                    slot="activator"
                                    class="status-item"
                                    @click="openCustomStatusModal(customStatus.forKey)"
                                >{{ customStatus.customStatusName }}</span>
                                <span>{{ $t('order.orders.setting.custom_status.dialog.edit_custom_status') }}</span>
                            </v-tooltip>
                            <v-spacer></v-spacer>
                            <v-tooltip top>
                                <v-btn
                                    small
                                    icon
                                    slot="activator"
                                    @click="deleteCustomStatus(customStatus.forKey, customStatus.isUseCustomStatus)"
                                >
                                    <v-icon>mdi-close</v-icon>
                                </v-btn>
                                <span>{{ $t('order.orders.setting.custom_status.delete') }}</span>
                            </v-tooltip>
                        </v-layout>
                    </v-container>
                </li>
            </draggable>
        </div>
        <confirm-dialog ref="confirm"></confirm-dialog>
        <custom-status-dialog
            ref="customStatus"
            @save="save"
        ></custom-status-dialog>
    </div>
</template>

<script>
import CustomStatusDialog from './CustomStatusDialog'
import store from '../../../../../stores/Order/Orders/Detail/store'
import ConfirmDialog from '../../../../Atoms/Dialogs/ConfirmDialog'

export default {
    components: {
        CustomStatusDialog,
        ConfirmDialog,
    },
    props: {
        orderId: { type: String, required: true },
    },
    computed: {
        customStatuses: function() {
            return store.state.processingData.customStatuses
        },
        deleteCustomStatusIds: function() {
            return store.state.processingData.deleteCustomStatusIds
        },
    },
    methods: {
        deleteCustomStatus: async function(forKey, isUseCustomStatus) {
            const messages = []
            if (isUseCustomStatus) messages.push(this.$t('order.orders.setting.custom_status.message.status_used_in_order_detail'))
            messages.push(this.$t('order.orders.setting.custom_status.message.delete'))
            const message = messages.join('<br>')
            if (!(await this.$refs.confirm.show(message))) return
            if (isUseCustomStatus) {
                if (!await this.$refs.confirm.show(`<h4>${this.$t('order.orders.setting.custom_status.message.last_confirmation')}</h4>
                    <div style="color: red;">${this.$t('order.orders.setting.custom_status.message.delete_last_confirmation')}</div>`,
                this.$t('common.button.delete'))) return
            }

            const deleteCustomStatusId = this.customStatuses.find(item => item.forKey === forKey)['customStatusId']
            if (deleteCustomStatusId !== null) store.commit('setDeleteCustomStatusIds', deleteCustomStatusId) // deleteCustomStatusId !== nullはDB登録済み

            store.commit('setCustomStatuses', this.customStatuses.filter(item => item.forKey !== forKey))
            await this.save()
        },
        openCustomStatusModal: function(forKey = null) {
            this.$refs.customStatus.open(forKey)
        },
        save: async function() {
            this.$emit('save')
        },
        onEnd: function (e) {
            if (e.newIndex !== e.oldIndex) {
                this.save()
            }
        },
    },
}
</script>

<style scoped>
.sortable-ghost {
    background-color: #dcdcdc;
}
.handle {
    color: rgba(0, 0, 0, 0.54);
    padding: 10px;
    cursor: move;
}
.status-item {
    color: #4DB6AC;
    max-width: 650px;
    padding: 10px;
    text-decoration-color: #4DB6AC;
}
.status-item:hover {
    text-decoration: underline;
    cursor: pointer;
}
.header-bottom-border {
    border-bottom: 1px solid #d7d7d7;
}
</style>

<template>
    <base-dialog
        v-if="dialog"
        :width="450"
    >
        <template slot="header">
            <span class="headline">
                {{ $t('order.order_details.dialog.business_selection.title') }}
            </span>
        </template>
        <template slot="content">
            <v-select
                v-model="businessId"
                :label="$t('order.order_details.dialog.business_selection.business')"
                :hint="$t('order.order_details.dialog.business_selection.business_select_remark')"
                persistent-hint
                :items="businesses"
                item-value="business_id"
                item-text="business_name"
                :menu-props="selectMenuProps"
                :no-data-text="$t('order.order_details.dialog.business_selection.no_data_text')"
            ></v-select>
        </template>
        <template slot="footer">
            <v-btn color="grey" dark @click="cancel()">{{ $t('common.button.cancel') }}</v-btn>
            <v-btn color="primary" :disabled="!isSelected" @click="next()">{{ $t('common.button.next') }}</v-btn>
        </template>
    </base-dialog>
</template>

<script>
import BaseDialog from '../../../Atoms/Dialogs/BaseDialog'
import requestInfoStore from '../../../../stores/Order/OrderDetails/Show/request-info'

export default {
    components: {
        BaseDialog,
    },
    data() {
        return {
            dialog: false,
            resolve: null,
            reject: null,
            businessId: null,
        }
    },
    created () {
        requestInfoStore.dispatch('searchBusinessesInfo')
    },
    computed: {
        businesses() {
            return requestInfoStore.state.businessesInCharge
        },
        isSelected() {
            return this.businessId != null
        },
        selectMenuProps() {
            return {
                'closeOnClick': false,
                'closeOnContentClick': false,
                'openOnClick': false,
                'maxHeight': 300,
                'nudge-top': -33
            }
        }
    },
    methods: {
        show() {
            this.dialog = true
            return new Promise((resolve, reject) => {
                this.resolve = resolve
                this.reject = reject
            })
        },
        cancel() {
            this.businessId = null
            this.next()
        },
        next() {
            this.resolve(this.businessId)
            this.dialog = false
        },
    }
}
</script>

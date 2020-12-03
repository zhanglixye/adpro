<template>
    <base-dialog
        v-if="dialog"
        :dark="dark"
        :disabled="disabled"
        :fullscreen="fullscreen"
        :full-width="fullWidth"
        :max-width="maxWidth"
        :scrollable="scrollable"
        :width="width"
    >
        <template slot="content">
            <div class="text-xs-center" v-html="message"></div>
        </template>
        <template slot="footer">
            <v-btn dark color="grey" @click.native="cancel">{{ $t('common.button.cancel') }}</v-btn>
            <v-btn color="primary" @click.native="ok">{{ okButtonName }}</v-btn>
        </template>
    </base-dialog>
</template>

<script>
import BaseDialog from './BaseDialog'
export default {
    components: {
        BaseDialog,
    },
    props: {
        dark: { type: Boolean, required: false, default: false },
        disabled: { type: Boolean, required: false, default: false },
        fullscreen: { type: Boolean, required: false, default: false },
        fullWidth: { type: Boolean, required: false, default: false },
        maxWidth: { type: [String, Number], required: false, default: 'none' },
        scrollable: { type: Boolean, required: false, default: false },
        width: { type: [String, Number], required: false, default: '600' },
    },
    data: () => ({
        dialog: false,
        message: null,
        okButtonName: null,
        resolve: null,
        reject: null,
    }),
    methods: {
        show (message, okButtonName = this.$t('common.button.ok')) {
            this.dialog = true
            this.message = message
            this.okButtonName = okButtonName
            return new Promise((resolve, reject) => {
                this.resolve = resolve
                this.reject = reject
            })
        },
        ok () {
            this.resolve(true)
            this.dialog = false
        },
        cancel () {
            this.resolve(false)
            this.dialog = false
        }
    },
}
</script>

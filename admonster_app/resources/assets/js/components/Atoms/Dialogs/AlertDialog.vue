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
            <v-btn color="primary" @click.native="ok">{{ $t('common.button.ok') }}</v-btn>
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
        width: { type: [String, Number], required: false, default: '300' },
    },
    data: () => ({
        dialog: false,
        message: null,
        okFunc: null,
    }),
    methods: {
        show (message, okFunc) {
            this.dialog = true
            this.message = message
            this.okFunc = okFunc
        },
        ok () {
            if (typeof this.okFunc === 'function') this.okFunc()
            this.dialog = false
        },
    }
}
</script>

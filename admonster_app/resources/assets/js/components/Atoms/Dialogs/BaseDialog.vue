<template>
    <v-dialog
        :dark="dark"
        :disabled="disabled"
        :fullscreen="fullscreen"
        :full-width="fullWidth"
        :max-width="maxWidth"
        persistent
        :scrollable="scrollable"
        :value="true"
        :width="width"
    >
        <v-card>
            <v-card-title v-if='this.$slots.header' class="pb-2">
                <slot name="header"></slot>
            </v-card-title>
            <v-card-text>
                <slot name="content" class="pt-2"></slot>
            </v-card-text>
            <v-card-actions class="btn-center-block">
                <slot name="footer"></slot>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
/** -- usage --
<base-dialog v-if="bool">
    <template slot="header">
        <!-- Custom -->
    </template>
    <template slot="content">
        <!-- Custom -->
    </template>
    <template slot="footer">
        <!-- Custom -->
    </template>
</base-dialog>
 */
import motionMixin from '../../../mixins/motionMixin'
export default {
    mixins: [motionMixin],
    props: {
        dark: { type: Boolean, required: false, default: false },
        disabled: { type: Boolean, required: false, default: false },
        fullscreen: { type: Boolean, required: false, default: false },
        fullWidth: { type: Boolean, required: false, default: false },
        maxWidth: { type: [String, Number], required: false, default: 'none' },
        scrollable: { type: Boolean, required: false, default: false },
        width: { type: [String, Number], required: false, default: 'auto' },
    },
    data: () => ({
        reject: null,
        resolve: null,
        scrollY: null,
    }),
    mounted: function() {
        this.open()
    },
    beforeDestroy: function() {
        this.close()
    },
    methods: {
        // ダイアログを開く
        open: function() {
            this.$_motionMixin_bodyPositionFixed()
        },
        // ダイアログを閉じる
        close: function() {
            this.$_motionMixin_bodyPositionStatic()
        },
    }

}
</script>

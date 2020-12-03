<template>
    <v-chip label outline :color="color">
        <v-icon left>{{ icon }}</v-icon>
        <span>{{ label }}</span>
    </v-chip>
</template>

<script>
export default {
    props: {
        status: { type: Number, required: true, default: _const.FLG.INACTIVE },
    },
    computed: {
        color () {
            if (this.status === _const.FLG.INACTIVE) return 'primary'
            if (this.status === _const.FLG.ACTIVE) return 'grey'
            return ''
        },
        icon () {
            if (this.status === _const.FLG.INACTIVE) return 'mdi-text-box'
            if (this.status === _const.FLG.ACTIVE) return 'mdi-text-box-check'
            return ''
        },
        label () {
            if (this.status === _const.FLG.INACTIVE) return Vue.i18n.translate('common.chips.none')
            if (this.status === _const.FLG.ACTIVE) return Vue.i18n.translate('common.chips.done')
            return ''
        }
    }
}
</script>

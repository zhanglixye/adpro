export default {
    data: () => ({
        scrollY: null,
    }),
    methods: {
        scrollToTop: function() {
            this.$vuetify.goTo('#app')
        },
        $_motionMixin_bodyPositionFixed: function() {
            this.scrollY = window.scrollY
            document.body.style.position = 'fixed'
            document.body.style.top = `-${this.scrollY}px`
            document.body.style.width = '100%'
        },
        $_motionMixin_bodyPositionStatic: function() {
            document.body.style.position = ''
            document.body.style.top = ''
            document.body.style.width = ''
            window.scrollTo(0, this.scrollY)
        }
    }
}

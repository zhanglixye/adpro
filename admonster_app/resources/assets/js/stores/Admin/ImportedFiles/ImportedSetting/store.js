import Vuex from 'vuex';
import createPersistedState from 'vuex-persistedstate'

const initialState = {
    subjects: [],
    items: [],
    fileInfo: {},
    headerRowNo: 1,
    dataStartRowNo: 2,
    sheetName: null,
    settingRules: {},
    readonly: false,
    orderId: null,
    filePath: null
}

const store = new Vuex.Store({
    state: JSON.parse(JSON.stringify(initialState)),
    mutations: {
        setSubjects(state, { params }) {
            state.subjects = params
        },
        setItems(state, { params }) {
            state.items = params
        },
        setFileInfo(state, { params }) {
            state.fileInfo = params
        },
        resetSubjects() {
            this.state.subjects = []
        },
        resetItems() {
            this.state.items = []
        },
        resetFileInfo () {
            this.state.fileInfo = {}
        },
        setSettingRule (state, { params }) {
            state.settingRules[params.index] = params.form
        },
        resetSettingRule () {
            this.state.settingRules = {}
        },
        setReadonly(state, isReadonly) {
            state.readonly = isReadonly
        },
        setOrderId(state, id) {
            state.orderId = id
        },
        setFilePath(state, filePath) {
            state.filePath = filePath
        },
    },
    actions: {},
    getters: {},
    plugins: [
        createPersistedState({
            key: 'ImportedFilePreference',
            paths: ['subjects', 'items', 'fileInfo', 'settingRules', 'headerRowNo', 'dataStartRowNo', 'filePath'],
            storage: window.sessionStorage,
        })
    ]

})
export default store

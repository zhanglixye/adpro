import Vuex from 'vuex';
import createPersistedState from 'vuex-persistedstate'

const initialState = {
    processingData: {
        orderId: null,
        orderDetailId: 0,
        // 件名
        subjectData: '',
        loading: false,
        customStatuses: [],
        // ラベルデータ
        labelData: {},
        // 各データ内容
        candidates: [],
        orderDetailData: {},
        startedAt: null,
        isAdmin: false
    },
    displayOrderDetails: {}
}

const store = new Vuex.Store({
    state: JSON.parse(JSON.stringify(initialState)),
    mutations: {
        setLoading({ processingData }, val) {
            processingData.loading = val
        },
        setLabelData({ processingData }, labelData) {
            processingData.labelData = labelData
        },
        setCandidates({ processingData }, candidates) {
            processingData.candidates = candidates
        },
        setOrderDetailData({ processingData }, orderDetailData) {
            processingData.orderDetailData = orderDetailData
        },
        setSubjectData({ processingData }, subjectData) {
            processingData.subjectData = subjectData
        },
        setStartedAt({ processingData }, startedAt) {
            processingData.startedAt = startedAt
        },
        setCustomStatuses({ processingData }, newCustomStatuses) {
            processingData.customStatuses = newCustomStatuses
        },
        setDisplayFormDetails({ displayOrderDetails }, displayOrderDetail) {
            Object.assign(displayOrderDetails, displayOrderDetail)
        },
        setOrderId({ processingData }, id) {
            Object.assign(processingData, { orderId: id })
        },
        setOrderDetailId({ processingData }, id) {
            Object.assign(processingData, {orderDetailId: id})
        },
        setIsAdmin({ processingData }, newIsAdmin) {
            processingData.isAdmin = newIsAdmin
        },
    },
    actions: {
        async getInitData({ commit }, { orderId, orderDetailId }) {
            commit('setLoading', true)
            const getDataParams = Vue.util.extend({}, { order_detail_id: orderDetailId, order_id: orderId })

            try {
                const res = await axios.post('/api/order/order_details/create', getDataParams)

                if (res.data.status !== 200) throw res.data.message
                // 参照渡し防止
                const customStatuses = JSON.parse(JSON.stringify(res.data['custom_statuses']))

                for (const customStatus of customStatuses) {
                    customStatus['customStatusId'] = customStatus['custom_status_id']
                    delete customStatus['custom_status_id']
                    customStatus['customStatusName'] = res.data['label_data']['ja'][customStatus['label_id']]
                    delete customStatus['label_id']
                    customStatus['selectAttributeId'] = customStatus['select_attribute_id']
                    delete customStatus['select_attribute_id']

                    for (const attribute of customStatus['attributes']) {
                        attribute['text'] = res.data['label_data']['ja'][attribute['label_id']]
                        delete attribute['label_id']
                    }

                    // カスタムステータス属性候補の先頭に空文字を追加
                    customStatus['attributes'].unshift({value: null, text: ''})
                }

                commit('setLabelData', res.data.label_data)
                commit('setCandidates', res.data.candidates)
                commit('setOrderDetailData', res.data.order_detail_data)
                commit('setSubjectData', res.data.subject)
                commit('setStartedAt', res.data.started_at)
                commit('setCustomStatuses', JSON.parse(JSON.stringify(customStatuses)))
                commit('setIsAdmin', res.data.is_admin)
                commit('setLoading', false)
            } catch (error) {
                console.log(error)
                commit('setLoading', false)
                throw error
            }
        },
        async saveOrderDetail({ commit, state }, { orderId, orderDetailId, subjectData, orderDetailData, customStatuses }) {
            commit('setLoading', true)
            const getDataParams = Vue.util.extend({}, {
                order_detail_id: orderDetailId,
                order_id: orderId,
                order_details_data: JSON.stringify(orderDetailData),
                subject_data: subjectData,
                started_at: state.processingData.startedAt['date'],
                custom_statuses: JSON.stringify(customStatuses),
            })

            try {
                const res = await axios.post('/api/order/order_details/save', getDataParams)
                if (res.data.status !== 200) throw res.data.message

                // 保存後に表示用データを更新する
                commit('setStartedAt', res.data.started_at)
                commit('setSubjectData', subjectData)
                commit('setOrderDetailData', JSON.parse(JSON.stringify(orderDetailData)))
                commit('setCustomStatuses', JSON.parse(JSON.stringify(customStatuses)))

                commit('setLoading', false)
            } catch (error) {
                commit('setLoading', false)
                if (error === 'no_admin_permission') throw Vue.i18n.translate('common.message.no_admin_permission')
                if (error === 'deleted_data') throw Vue.i18n.translate('order.order_details.dialog.status.to_deleted.failure')
                if (error === 'updated_by_others') throw Vue.i18n.translate('common.message.updated_by_others')
                throw Vue.i18n.translate('common.message.internal_error')
            }
        },
        async deleteOrderDetail({ commit, state }) {
            commit('setLoading', true)
            const getDataParams = Vue.util.extend({}, {
                order_detail_ids: [state.processingData.orderDetailId],
                order_id: state.processingData.orderDetailData.order_id,
                started_at: state.processingData.startedAt['date'],
            })
            try {
                const res = await axios.post('/api/order/order_details/bulk_delete', getDataParams)
                if (res.data.status !== 200) throw res.data.message
                commit('setLoading', false)
            } catch (error) {
                commit('setLoading', false)
                if (error === 'no_admin_permission') throw Vue.i18n.translate('common.message.no_admin_permission')
                if (error === 'deleted_data') throw Vue.i18n.translate('order.order_details.dialog.status.to_deleted.failure')
                if (error === 'updated_by_others') throw Vue.i18n.translate('common.message.updated_by_others')
                throw Vue.i18n.translate('common.message.internal_error')
            }
        },
        async saveOrderDetailSystemItems({ commit, state }, { orderDetailId, subjectData, orderDetailData, customStatuses }) {
            commit('setLoading', true)
            const getDataParams = Vue.util.extend({}, {
                order_detail_id: orderDetailId,
                subject_data: subjectData,
                selected_system_status_id: orderDetailData.order_detail_is_active,
                started_at: state.processingData.startedAt['date'],
                custom_statuses: JSON.stringify(customStatuses),
            })

            try {
                const res = await axios.post('/api/order/order_details/save_system_items', getDataParams)
                if (res.data.status !== 200) throw res.data.message

                // 保存後に表示用データを更新する
                commit('setStartedAt', res.data.started_at)
                commit('setSubjectData', subjectData)
                commit('setOrderDetailData', JSON.parse(JSON.stringify(orderDetailData)))
                commit('setCustomStatuses', JSON.parse(JSON.stringify(customStatuses)))

                commit('setLoading', false)
            } catch (error) {
                commit('setLoading', false)
                if (error === 'no_admin_permission') throw Vue.i18n.translate('common.message.no_admin_permission')
                if (error === 'deleted_data') throw Vue.i18n.translate('order.order_details.dialog.status.to_deleted.failure')
                if (error === 'updated_by_others') throw Vue.i18n.translate('common.message.updated_by_others')
                throw Vue.i18n.translate('common.message.internal_error')
            }
        },
        getDisplayOrderDetail({ state }, orderDetailId) {
            if (orderDetailId in state.displayOrderDetails) {
                return state.displayOrderDetails[orderDetailId]
            }
            return {}
        },
    },
    getters: {},
    plugins: [
        createPersistedState({
            key: 'initialDisplayOrderDetails',
            paths: ['displayOrderDetails'],
            storage: window.localStorage,
        })
    ]
})
export default store

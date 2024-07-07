export default {
    computed: {
        productCriteria() {
            const criteria = this.$super('productCriteria')
            criteria.addAssociation('bundleContents')
            return criteria;
        },
    }
}

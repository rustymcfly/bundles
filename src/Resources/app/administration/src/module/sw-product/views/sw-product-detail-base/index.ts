import template from './template.html.twig'

const {Data} = Shopware

const {Criteria} = Data
export default {
    template,
    data() {
        return {
            bundleContents: null
        }
    },
    watch: {
        product(value) {
            if (value) {
                this.getBundleContents()
            }
        }
    },
    methods: {
        async getBundleContents() {
            this.bundleContents = await this.bundleRepository.search(this.bundleContentCriteria)
        }

    },
    computed: {
        bundleRepository() {
            return this.repositoryFactory.create('rustymcfly_bundles_mapping')
        },
        bundleContentCriteria() {
            const criteria = new Criteria()
            criteria.addAssociation('product')
            criteria.addFilter(Criteria.equals('parentId', this.product.id))
            return criteria
        },
        bundleColumns() {
            return [
                {
                    property: 'product.translated.name',
                    label: 'name'
                },
                {
                    property: 'quantity',
                    label: 'quantity',
                    inlineEdit: 'number'
                }
            ]
        }
    }
}

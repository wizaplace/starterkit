import { SearchClient } from '../components/SearchClient';

// helpers
const templateContainerId = "search-bar";
const containerIsLoaded = $(`#${templateContainerId}`).length;
let instance = null; // will hold a reference to this class as a singleton

export class SearchBar {

    constructor() {

        if(! instance) {

            instance = this; // set singleton

            this.search = new SearchClient(Routing.generate('api_search'));
            this.urlParameters = this.search.restoreSearchFromUrl();

            this.searchInit();
        }

        return instance;
    }

    static init() {

        // code is executed only on this template
        if(containerIsLoaded) {
            return new this; // return singleton instance of this class
        }
    }

    // search related functions (filters, facets, pagination, etc.)
    searchInit() {


        // as 'this' here represents the Vue object, 'instance' is used to reference the SearchPage class
    //     let vm = new Vue({
    //         el: `#${templateContainerId}`,
    //         delimiters: ['${','}'], // avoid conflict with twig '{{ }}' syntax
    //         data: {
    //             currentCategoryId: {{ currentCategory|default(null) ? currentCategory.id : 'null' }},
    //     currentCategoryName: '{{ currentCategory|default(null) ? currentCategory.name|e('js') : 'all_categories'|trans|e('js') }}',
    //         query: '{{ searchQuery|default('')|e('js') }}',
    // },
    //     methods: {
    //         selectCategory: function (categoryId, categoryName) {
    //             this.currentCategoryId = categoryId;
    //             this.currentCategoryName = categoryName;
    //         },
    //         showCategories: function() {
    //             $(".category-filter").toggleClass("hidden");
    //         }
    //     }
    // });

    }
}

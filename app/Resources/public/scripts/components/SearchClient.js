/**
 * Search API.
 */

export class SearchClient {
    
    constructor(productSearchEndpoint, productAutocompleteEndpoint) {
        this.productSearchEndpoint = productSearchEndpoint;
        this.productAutocompleteEndpoint = productAutocompleteEndpoint;
    }

    /**
     * @param {string} query
     * @param {int} page
     * @param {int} resultsPerPage
     * @param {Object} filters Key-value map of filters
     * @param {Object} sorting Key-value map of sorting
     * @param {Object} geoFilter Contains lat, lng, radius and label
     * @param {Object} extra Key-value map of extra parameters specific to the search engine implementation
     * @param {function} success Signature: function(results)
     */
    searchProducts(query, page, resultsPerPage, filters, sorting, geoFilter, extra, success) {
        let parameters = {
            query: query,
            page: page,
            resultsPerPage: resultsPerPage,
            extra: extra
        };

        for (let filterName in filters) {
            if (filters.hasOwnProperty(filterName)) {
                let key = 'filters[' + filterName + ']';
                parameters[key] = filters[filterName];
            }
        }

        if (sorting !== null) {
            parameters.sorting = sorting;
        }

        if (geoFilter !== null) {
            parameters.geo = geoFilter;
        }

        let request = jQuery.get(this.productSearchEndpoint, parameters);
        request.done(function (data) {
            success(data);
        });

        request.fail(function (jqXHR, textStatus) {
            console.error('Error while searching products: ' + textStatus);
        });
    }

    /**
     * @param {string} query
     * @param {function} success Signature: function(results)
     */    
    autocomplete(query, success) {
        let request = jQuery.get(this.productAutocompleteEndpoint, {
            query: query
        });
        
        request.done(function (data) {
            success(data);
        });
        
        request.fail(function (jqXHR, textStatus) {
            console.error('Error while autocompleting products: ' + textStatus);
        });
    }

    /**
     * Save search parameters in the URL (as query parameters).
     *
     * Requires the URI.js library.
     */
    saveSearchInUrl(query, page, resultsPerPage, filters, sorting, geoFilter) {
        let uri = new URI();

        // remove filters from current query (so we can set only the ones we were passed in params)
        let currentQuery = uri.search(true);
        
        for (let name in currentQuery) {
            if (!currentQuery.hasOwnProperty(name)) {
                continue;
            }

            if(isSpecialFilter(name) || isInt(name) || name.indexOf("sort") === 0) {
                uri.removeQuery(name);
            }
        }

        for (let name in filters) {
            if (!filters.hasOwnProperty(name)) {
                continue;
            }
            
            let value = filters[name];
            
            // Numeric filter
            if (value.hasOwnProperty('min') || value.hasOwnProperty('max')) {
                if (value.hasOwnProperty('min')) {
                    uri.setQuery(name + '[min]', filters[name]['min']);
                }
                
                if (value.hasOwnProperty('max')) {
                    uri.setQuery(name + '[max]', filters[name]['max']);
                }
                
            } else {
                uri.setQuery(name, filters[name]);
            }
        }
        
        if (query) {
            uri.setQuery('q', query);
        } else {
            uri.removeQuery('q');
        }
        
        if (page != 1) {
            uri.setQuery('page', page);
            uri.setQuery('perPage', resultsPerPage);
        } else {
            uri.removeQuery('page');
            uri.removeQuery('perPage');
        }
        
        for (let sortName in sorting) {
            if (sorting.hasOwnProperty(sortName)) {
                uri.setQuery('sort[' + sortName +']', sorting[sortName]);
            }
        }
        
        if (geoFilter) {
            uri.setQuery('geo[lat]', geoFilter.lat);
            uri.setQuery('geo[lng]', geoFilter.lng);
            uri.setQuery('geo[radius]', geoFilter.radius);
            uri.setQuery('geo[label]', geoFilter.label);
        } else {
            uri.removeQuery('geo[lat]');
            uri.removeQuery('geo[lng]');
            uri.removeQuery('geo[radius]');
            uri.removeQuery('geo[label]');
        }
        
        history.replaceState({}, document.title, uri.toString());
    }

    /**
     * Restore search parameters from the URL (query parameters).
     *
     * Requires the URI.js library.
     *
     * @return {{ query: String, page: Number, resultsPerPage: Number, sorting: Object, filters: Object }}
     */
    restoreSearchFromUrl() {
        // Decode URI parameters, including arrays (which URI.js doesn't do)
        function getUriParameters() {
            let uriParameters = (new URI()).query(true);
            let parameters = {};
            for (let name in uriParameters) {
                if (!uriParameters.hasOwnProperty(name)) {
                    continue;
                }
                // Ignore empty parameters
                if (!uriParameters[name]) {
                    continue;
                }
                // Decode arrays (does not support nested arrays or indexed arrays yet)
                let matches = name.match(/(.+)\[(.+)\]/);
                if (matches) {
                    if (!parameters.hasOwnProperty(matches[1])) {
                        parameters[matches[1]] = {};
                    }
                    parameters[matches[1]][matches[2]] = uriParameters[name];
                } else {
                    parameters[name] = uriParameters[name];
                }
            }
            return parameters;
        }

        let result = {
            query: null,
            page: null,
            resultsPerPage: null,
            sorting: null,
            filters: null,
            geoFilter: null,
        };

        // Read parameters from the URL
        let parameters = getUriParameters();
        for (let name in parameters) {
            if (!parameters.hasOwnProperty(name)) {
                continue;
            }
            let value = parameters[name];
            if (name === 'q') {
                result.query = value;
            } else if (name === 'page') {
                result.page = value;
            } else if (name === 'perPage') {
                result.resultsPerPage = value;
            } else if (name === 'sort') {
                result.sorting = value;
            } else if (name === 'geo') {
                result.geoFilter = value;
            } else if (isSpecialFilter(name) || isInt(name)) {
                // Filters
                if (result.filters === null) {
                    result.filters = {};
                }
                result.filters[name] = value;
            }
        }
        return result;
    }
}

// 'private' functions (callable only inside this module)
function isSpecialFilter(name) {
    return ["categories", "companies", "companyType", "price"].indexOf(name) !== -1
}

function isInt(val) {
    return (typeof val === 'number' || typeof val === 'string') && val % 1 === 0
}

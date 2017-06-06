export class Helper {

    static formatPrice(price) {

        if( ! $.isNumeric(price) ) {
            return;
        }

        price = price.toFixed(2) + '';
        return price.replace('.', ',') + '€';
    }
}
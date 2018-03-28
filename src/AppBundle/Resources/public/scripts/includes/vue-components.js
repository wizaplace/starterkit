/** Vendor - Products - Creation **/

 Vue.component('form-input-text', {
    template: `
        <div>
            <label :for="item.id" v-text="item.name"></label><span v-if="item.required">*</span>
            <input type="text" :id="item.id" :name="item.inputName" required v-if="item.required">
            <input type="text" :id="item.id" :name="item.inputName" v-else>
        </div>`,
    props: ['item']
});


Vue.component('form-input-number', {
    template: `
        <div>
            <label :for="item.id" v-text="item.name"></label><span v-if="item.required">*</span>
            <input type="number" :step="item.step" :id="item.id" :name="item.inputName" :value="item.defaultValue" required v-if="item.required">
            <input type="number" :step="item.step" :id="item.id" :name="item.inputName" :value="item.defaultValue" v-else>
        </div>`,
    props: ['item']
});


Vue.component('form-input-checkbox', {
    template: `
        <div>
            <label :for="item.id" v-text="item.name"></label><span v-if="item.required">*</span>
            <input type="checkbox" :id="item.id" :name="item.inputName" :value="item.checked" :checked="item.checked" :required="item.required">
        </div>`,
    props: ['item']
});


Vue.component('form-input-file', {
    template: `
        <div>
            <label v-text="item.name"></label><span v-if="item.required">*</span>
            <div v-for="i in count">
                <input type="file" :name="generateInputName(item.inputName, i)" :required="item.required">
                <div v-if="item.multiple === true">
                    <input type="button" :value="'-'" @click="decrementCount(i)" v-if="i !== 1">
                    <input type="button" :value="'+'" @click="incrementCount(i)" v-if="i === count">
                </div>
            </div>
        </div>`,
    props: ['item'],
    data: function() {
        return {
            count: 1,
        };
    },
    methods: {
        incrementCount(i) {
            const inputName = this.generateInputName(this.item.inputName, i);
            if ($('input[name='+inputName+']').val() !== '') {
                this.count++;
            } else {
                notification.createAlert(this.item.error, 'danger');
            }
        },
        decrementCount() {
            this.count--;
        },
        generateInputName(name, count) {
            return `${name}-${count}`;
        }
    }
});


Vue.component('form-select', {
    template: `
        <div>
            <label :for="item.id" v-text="item.name"></label><span v-if="item.required">*</span>
            <select :id="item.id" :name="item.inputName" :required="item.required">
                <option>Selectionner</option>
                <option v-for="option in item.options" :value="option.value" v-text="option.name"></option>
            </select>
        </div>`,
    props: ['item']
});


Vue.component('form-object', {
    template: `
        <div>
            <label v-text="item.title"></label><span v-if="item.required">*</span>
            <div v-for="object in item.object">
                <form-input-text :item="object" v-if="object.type === 'text'"></form-input-text>
                <form-input-number :item="object" v-if="object.type === 'number'"></form-input-number>
                <form-input-checkbox :item="object" v-if="object.type === 'checkbox'"></form-input-checkbox>
                <form-input-file :item="object" v-if="object.type === 'file' && !object.multiple"></form-input-file>
                <form-select :item="object" v-if="object.type === 'select'"></form-select>
            </div>
        </div>`,
    props: ['item'],
});

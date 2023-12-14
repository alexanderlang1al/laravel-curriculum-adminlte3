<template>
    <div class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <b v-if="this.method === 'post'" class="modal-title">
                        {{ trans('global.logbook.create') }}
                    </b>
                    <b v-else>
                        {{ trans('global.logbook.edit') }}
                    </b>
                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="card mb-0">
                        <div class="card-header border-bottom"
                            data-card-widget="collapse">
                            <h5 class="card-title">
                                Allgemein
                            </h5>
                        </div>
                        <div class="card-body pb-0">
                            <div class="form-group">
                                <input
                                    type="text"
                                    id="title"
                                    name="title"
                                    class="form-control"
                                    v-model.trim="form.title"
                                    :placeholder="trans('global.title')"
                                    required
                                />
                                <p class="help-block" v-if="form.errors.title" v-text="form.errors.title[0]"></p>
                            </div>
                            <div class="form-group">
                                <textarea
                                    id="description"
                                    name="description"
                                    :placeholder="trans('global.description')"
                                    class="form-control description "
                                    v-model.trim="form.description"
                                ></textarea>
                                <p class="help-block" v-if="form.errors.description" v-text="form.errors.description[0]"></p>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-0">
                        <div class="card-header border-bottom"
                            data-card-widget="collapse">
                            <h5 class="card-title">
                                Darstellung
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <span
                                    :style="{borderColor: textColor, height: '42px'}">
                                    <color-picker-input
                                        v-model="form.color">
                                    </color-picker-input>
                                </span>
                                <div class="dropdown">
                                    <button
                                        class="btn btn-default"
                                        style="width: 42px; padding: 6px 0px;"
                                        type="button"
                                        data-toggle="dropdown"
                                        aria-expanded="false"
                                    >
                                        <i :class="form.css_icon + ' pt-2'"></i>
                                    </button>
                                    <font-awesome-picker
                                        class="dropdown-menu dropdown-menu-right"
                                        style="min-width: 400px;"
                                        :searchbox="trans('global.select_icon')"
                                        v-on:selectIcon="setIcon"
                                    ></font-awesome-picker>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button"
                            class="btn btn-default"
                            data-dismiss="modal">
                        {{ trans('global.cancel') }}
                    </button>
                    <button type="button"
                            class="btn btn-primary"
                            data-dismiss="modal"
                            @click="submit()">
                        {{ trans('global.save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import Form from "form-backend-validation";
import FontAwesomePicker from "../../../views/forms/input/FontAwesomePicker";

export default {
    name: 'LogbookCreate',
    props: {
        logbook: {},
        method: '',
    },
    data() {
        return {
            component_id: this._uid,
            requestUrl: '/logbooks',
            form: new Form({
                'id': '',
                'title':  '',
                'description':  '',
                'color':'#27AF60',
                'css_icon': 'fa fa-book',
            }),
        };
    },
    watch: {
        logbook: function(newVal, oldVal) {
            this.form.id = newVal.id;
            this.form.title = newVal.title;
            this.form.description = this.htmlToText(newVal.description);
            this.form.color = newVal.color;
            this.form.css_icon = newVal.css_icon;
        },
        method: function (newVal, oldVal) {
            if (newVal == 'post') {
                this.form.reset();
            }
        }
    },
    computed:{
        textColor: function(){
            return this.$textcolor(this.form.color, '#333333');
        }
    },
    methods: {
        setIcon(selectedIcon) {
            this.form.css_icon = 'fa fa-' + selectedIcon.className;
        },
        submit() {
            let method = this.method.toLowerCase();

            if (method === 'patch') {
                axios.patch(this.requestUrl + '/' + this.form.id, this.form)
                    .then(res => { // Tell the parent component we've updated a task
                        this.$eventHub.$emit("logbook-updated", res.data.logbook);
                    })
                    .catch(error => { // Handle the error returned from our request
                        console.log(error);
                    });
            } else {
                axios.post(this.requestUrl, this.form)
                    .then(res => {
                        window.location = res.data.message;
                        //this.$eventHub.$emit("logbook-added", res.data.message);
                    })
                    .catch(error => { // Handle the error returned from our request
                        console.log(error)
                    });
            }
        },
    },
    components: {
        FontAwesomePicker,
    }
}
</script>
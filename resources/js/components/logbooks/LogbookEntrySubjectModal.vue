<template>
    <modal
        id="logbook-entry-subject-modal"
        name="logbook-entry-subject-modal"
        :adaptive=true
        draggable=".draggable"
        :resizable=true
        @before-open="beforeOpen"
        @opened="opened"
        @before-close="beforeClose"
        height="auto"
        style="z-index: 1100;">
        <div class="card" style="margin-bottom: 0px !important">
            <div class="card-header">
                <h3 class="card-title">
                    <span>{{ trans("global.logbookEntry.subject") }}</span>
                </h3>
                <div class="card-tools">
                     <button type="button" class="btn btn-tool draggable" >
                        <i class="fa fa-arrows-alt"></i>
                     </button>
                     <button type="button" class="btn btn-tool" data-widget="remove" @click="close()">
                        <i class="fa fa-times"></i>
                     </button>
                 </div>
            </div>
            <div class="card-body" style="max-height: 80vh; overflow-y: auto;">
                <label for="subjects">{{ trans("global.logbookEntry.fields.subject") }}</label>
                <select :id="'subject_' + id"
                        name="subjects"
                        class="form-control"
                        @click="initSelect2()"
                >
                    <option default>{{ subject }}</option>
                </select>
            </div>
        </div>
    </modal>
</template>
<script>
export default {
    data() {
        return {
            id: Number,
            subject: String,
        };
    },
    methods: {
        initSelect2() {
            $('#subject_' + this.id).select2({
                dropdownParent: $(".v--modal-overlay"),
                allowClear: false,
                ajax: {
                    url: "/subjects",
                    dataType: 'json',
                    data: function (params) {
                        return {
                            term: params.term || '',
                            page: params.page || 1
                        }
                    },
                    cache: true
                },
            }).on('select2:open', () => {
                document.querySelector('.select2-search__field').focus();
            }).on('select2:select', (e) => {
                axios.post('/logbookEntries/setSubject?id=' + this.id, {
                    'subject_id' : e.params.data.id,
                });

                this.$eventHub.$emit('updateSubjectBadge', {
                    entry_id: this.id,
                    subject_id: e.params.data.id,
                    title: e.params.data.text,
                });
                this.$modal.hide('logbook-entry-subject-modal');
            });
        },
        beforeOpen(event) {
            this.id = event.params.id;
            this.subject = event.params.subject ?? '';
        },
        opened() {
            // select-element always throws error on first click, so click it instantly on popup
            $('#subject_' + this.id).click();
        },
        beforeClose() {},
        close() {
            this.$modal.hide('logbook-entry-subject-modal');
        },
    },
}
</script>

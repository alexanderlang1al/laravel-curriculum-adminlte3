<template>
    <div class="row">
        <div v-permission="'logbook_entry_create'"
             class="col-md-12 pl-3 pt-0 pb-2"
        >
            <button id="add-logbook-entry"
                    class="btn btn-success"
                    @click.prevent="open('logbook-entry-modal')"
            >
                {{ trans('global.logbookEntry.create') }}
            </button>
            <button
                id="print-logbook"
                type="button"
                class="pull-right btn btn-tool pt-3"
                @click.p.prevent="togglePrintOptions();"
            >
                <i class="fa fa-print"></i>
            </button>
        </div>

        <div class="col-md-12"
             v-if="showPrintOptions"
        >
            <LogbookPrintOptions
                :logbook="logbook"
                :period="period"
            ></LogbookPrintOptions>
        </div>

        <div class="col-md-12">
            <LogbookEntry
                v-for="(entry, index) in entries"
                v-bind:key="entry.id"
                :first=" index === 0 "
                :entry="entry"
                :search="search"
                :logbook="logbook"
            ></LogbookEntry>
        </div>
        <!-- /.col -->
        <logbook-entry-modal></logbook-entry-modal>
        <lms-modal></lms-modal>
        <LogbookIndexAddWidget
            :visible="false"
            v-can="'kanban_create'"
        />
    </div>
</template>

<script>
import LogbookEntry from '../logbooks/LogbookEntry.vue';
import LogbookPrintOptions from "./LogbookPrintOptions";
import LogbookIndexAddWidget from "./LogbookIndexAddWidget";

export default {
    props: {
        'logbook': Object,
        'period': Object
    },
    data () {
        return {
            entries: [],
            search: '',
            showPrintOptions: false,
        };
    },

    methods: {
        open(modal) {
            this.$modal.show(modal, {'logbook_id': this.logbook.id});
        },
        togglePrintOptions() {
            this.showPrintOptions = !this.showPrintOptions;
        }

    },
    mounted() {
        this.entries = this.logbook.entries;

        this.$eventHub.$on('addLogbookEntry', (newEntry) => {
            if (newEntry.subject == undefined) {
                newEntry.subject = {
                    id: null,
                    title: null,
                };
            }
            this.entries.unshift(newEntry);       // Add newly created entry
        });
        this.$eventHub.$on('updateLogbookEntry', (updatedEntry) => {
            const index = this.entries.findIndex(            // Find the index of the status where we should replace the item
                entry => entry.id === updatedEntry.id
            );
            // only update entry, do not manipulate relations.
            this.entries[index].title = updatedEntry.title;
            this.entries[index].description = updatedEntry.description;
            this.entries[index].updated_at = updatedEntry.updated_at;
            this.entries[index].begin = updatedEntry.begin;
            this.entries[index].end = updatedEntry.end;
        });
        this.$eventHub.$on('updateSubjectBadge', (updatedEntry) => {
            const index = this.entries.findIndex(
                entry => entry.id === updatedEntry.entry_id
            );
            this.entries[index].subject = {
                id: updatedEntry.subject_id,
                title: updatedEntry.title,
            }
        });
        this.$eventHub.$emit('showSearchbar');

        this.$on('deleteLogbookEntry', function (deletedEntry) {
            let index = this.entries.indexOf(deletedEntry);
            this.entries.splice(index, 1);
        });
    },
    components: {
        LogbookPrintOptions,
        LogbookEntry,
        LogbookIndexAddWidget,
    }
}
</script>

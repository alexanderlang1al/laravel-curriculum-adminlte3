<template>
    <div>
        <div v-for="objective in objectives" :id="'enablingObjective_' + objective.id" class="box-objective" >
            <ObjectiveBox type="enabling"
                  :objective="objective"
                  :settings="settings"
                  :editable="editable"
                  :max_id="localSettings.last"
            ></ObjectiveBox>
        </div>

        <div id="createEnablingRow"
             v-can="'curriculum_edit'"
             v-if="settings.edit === true">
            <ObjectiveBox type="createenabling"
                :objective="{'curriculum_id': terminalobjective.curriculum_id, 'terminal_objective_id': terminalobjective.id}"
                :settings="settings"
                :max_id="localSettings.last"
            ></ObjectiveBox>
        </div>
    </div>
</template>

<script>
const ObjectiveBox =
    () => import('./ObjectiveBox');
   // import ObjectiveBox from './ObjectiveBox'

export default {
    props: {
        objectives: Array,
        terminalobjective: Object,
        settings: Object,
        editable: {
            default: false
        },
    },
    data() {
        return {
            localSettings: {
                'last': null,
            },
        }
    },
    mounted() {
        this.settings = this.settings;

        if (this.objectives.length != 0) {
            this.localSettings.last = this.objectives[this.objectives.length-1].id;
        }
    },

    components: {
        ObjectiveBox
    }
}
</script>

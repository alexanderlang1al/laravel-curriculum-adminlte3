<template>
    <div>
        <table class="table m-0 border-top-0"
            style="border-top: 0"
            v-permission="'achievement_create_self_assessment'"
            v-hide-if-permission="'achievement_access'"
        >
            <thead class=" border-top-0">
                <tr class="border-top-0">
                    <th class="border-top-0">{{trans('global.name')}}</th>
                    <th class="border-top-0">{{trans('global.created_at')}}</th>
                    <th class="border-top-0">{{trans('global.updated_at')}}</th>
                    <th class="border-top-0">{{trans('global.teacher')}}</th>
                    <th class="border-top-0">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td v-if="objective.achievements[0]">
                        {{ objective.achievements[0].user.firstname }} {{ objective.achievements[0].user.lastname }}
                    </td>
                    <td v-else></td>
                    <td v-if="objective.achievements[0]">
                        {{ objective.achievements[0].created_at }}
                    </td>
                    <td v-else></td>
                    <td v-if="objective.achievements[0]">
                        {{ objective.achievements[0].updated_at }}
                    </td>
                    <td v-else></td>
                    <td v-if="objective.achievements[0]">
                        {{ objective.achievements[0].owner.firstname }} {{ objective.achievements[0].owner.lastname }}
                    </td>
                    <td v-else></td>
                    <td>
                        <AchievementIndicator
                            v-if="objective.achievements[0]"
                            v-permission="'achievement_create'"
                            :objective="objective"
                            :type="type"
                            :users="[objective.achievements[0].user.id]"
                            :settings="{'achievements' : false, 'edit': false}">
                        </AchievementIndicator>
                        <AchievementIndicator
                            v-else
                            v-permission="'achievement_create'"
                            :objective="objective"
                            :type="type"
                            :settings="{'achievements' : false, 'edit': false}">
                        </AchievementIndicator>
                    </td>
                </tr>
            </tbody>
        </table>

        <div v-if="this.groups.length"
            v-permission="'achievement_access'"
            class="form-group p-2"
        >
            <label for="achievements_group">
                {{ trans('global.group.title_singular') }}
            </label>
            <select name="achievements_group[]"
                    id="achievements_group"
                    class="form-control select2 "
                    style="width:100%;"
                    multiple=false
                    v-model="groups"
            >
                <option v-for="(item,index) in groups" v-bind:value="item.id">{{ item.title }}</option>
            </select>
        </div>

        <table class="table m-0 border-top-0"
            style="border-top: 0"
            v-if="this.users.length"
            v-permission="'achievement_access'"
        >
            <thead class=" border-top-0">
                <tr class="border-top-0">
                    <th class="border-top-0">{{trans('global.name')}}</th>
                    <th class="border-top-0">{{trans('global.created_at')}}</th>
                    <th class="border-top-0">{{trans('global.updated_at')}}</th>
                    <th class="border-top-0">{{trans('global.teacher')}}</th>
                    <th class="border-top-0">{{trans('global.notes')}}</th>
                    <th class="border-top-0">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in users">
                    <td>{{ user.firstname }} {{ user.lastname }}</td>
                    <td>
                        <span v-if="currentUser(user.id).achievements[0]">
                            {{ currentUser(user.id).achievements[0].created_at }}
                        </span>
                    </td>
                    <td>
                        <span v-if="currentUser(user.id).achievements[0]">
                            {{ currentUser(user.id).achievements[0].updated_at }}
                        </span>
                    </td>
                    <td>
                        <span v-if="currentUser(user.id).achievements[0]">
                            {{ currentUser(user.id).achievements[0].owner.firstname }} {{ currentUser(user.id).achievements[0].owner.lastname }}
                        </span>
                    </td>
                    <td v-if="currentUser(user.id).achievements[0]">
                        <i style="font-size:18px;"
                            class="far fa-sticky-note text-muted pointer"
                            @click.prevent="$modal.show('note-modal', {'method': 'post', 'notable_type': 'App\\Achievement', 'notable_id': currentUser(user.id).achievements[0].id,'show_tabs': false}) ">
                        </i>
                    </td>
                    <td v-else></td>
                    <td>
                        <AchievementIndicator
                            v-permission="'achievement_create'"
                            :objective="currentUser(user.id)"
                            :type="type"
                            :users="[user.id]"
                            :settings="{'achievements' : false, 'edit': false}">
                        </AchievementIndicator>
                    </td>
                </tr>
            </tbody>
        </table>
        <note-modal></note-modal>
    </div>
</template>


<script>
const AchievementIndicator =
    () => import('./AchievementIndicator');
    //import AchievementIndicator from './AchievementIndicator';

export default {
    props: {
        objective: {},
        type:{},
        settings:{}
    },
    data() {
        return {
            objectiveWithAchievement : {},
            groups: [],
            users: {},
            selectedGroup: null,
            // currentUsersObjective: [],
            errors: {},
        }
    },
    methods: {
        loaderEvent(){
            axios.get('/enablingObjectives/' + this.objective.id + '/achievements/' + this.selectedGroup)
                .then(response => {
                    this.processResponse(response);
                }).catch(e => {
                this.errors = e.response.data.errors;
            });
        },
        currentUser(id)
        {
            let currentUsersObjective = JSON.parse(JSON.stringify(this.objectiveWithAchievement));
            const achievement = currentUsersObjective.achievements.find(e => e.user_id == id);
            currentUsersObjective.achievements = [achievement];

            return currentUsersObjective;
        },
        processResponse(response){
            this.objectiveWithAchievement = response.data.objective;
            this.groups = response.data.groups;
            this.users  = response.data.users;
            if (this.selectedGroup == null){
                this.$nextTick(() => {
                    $("#achievements_group").select2({
                        dropdownParent: $("#achievements_group").parent(),
                        allowClear: false
                    }).on('select2:select', function () {
                        this.selectedGroup = $("#achievements_group").val();
                        this.loaderEvent();
                    }.bind(this))
                    .on('select2:unselect', function () {
                        this.selectedGroup = null;
                        this.objectiveWithAchievement.achievements = [];
                    }.bind(this));
                });
            }
        }
    },
    mounted() {},
    components: {
        AchievementIndicator
    }
}
</script>
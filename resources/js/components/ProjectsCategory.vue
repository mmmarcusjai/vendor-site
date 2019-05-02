<template>
    <div class="container">
        <div class="row mt-5" v-if="$gate.isSadminOrAdmin()">
          <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Companies Table</h3>

                    <div class="card-tools">
                        <button class="btn btn-success" @click="openModal()">Add New <i class="fas fa-user-plus fa-fw"></i></button>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body table-responsive p-0">
                    <vuetable ref="vuetable"
                        :api-mode="false"
                        :fields="fields"
                        :per-page="perPage"
                        :data-manager="dataManager"
                        pagination-path="pagination"
                        @vuetable:pagination-data="onPaginationData"
                    >
                        <div slot="modify" slot-scope="props">
                            <a href="#" @click="openModal(props.rowData)">
                                <i class="fas fa-pen blue"></i>
                            </a>
                            |
                            <a href="#" @click="deleteUser(props.rowData.id)">
                                <i class="fas fa-trash-alt red"></i>
                            </a>
                        </div>
                    </vuetable>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    <vuetable-pagination ref="pagination"
                        @vuetable-pagination:change-page="onChangePage"
                    ></vuetable-pagination>
                </div>
            </div>
            <!-- /.card -->
          </div>
        </div>

        <div v-if="!$gate.isSadminOrAdmin()">
            <not-found></not-found>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" v-show="!editmode" id="addNewLabel">Add User</h5>
                        <h5 class="modal-title" v-show="editmode" id="addNewLabel">Update User's Info</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="checkForm()" @keydown="form.onKeydown($event)">
                        <div class="modal-body">
                            <div class="form-group">
                                <input v-model="form.category_name" v-validate="'required'" type="text" name="category_name"
                                    placeholder="Category Name"
                                    class="form-control" :class="{ 'is-invalid': form.errors.has('category_name'), 'is-invalid': errors.has('category_name') }">
                                <has-error :form="form" field="category_name"></has-error>
                                <div v-show="errors.has('category_name')" class="help-block invalid-feedback">{{ errors.first('category_name') }}</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                            <button v-show="!editmode" :disabled="form.busy" type="submit" class="btn btn-primary">Add</button>
                            <button v-show="editmode" :disabled="form.busy" type="submit" class="btn btn-success">Update</button>
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>



</template>

<script>
    import Vuetable from "vuetable-2";
    import VuetablePagination from "vuetable-2/src/components/VuetablePagination";

    export default {
        components: {
            Vuetable,
            VuetablePagination
        },
        data() {
            return {
                editmode: false,
                form: new Form({
                    category_name : '',
                }),
                fields: [{
                    name: 'id',
                    title: 'ID',
                    sortField: 'id'
                },
                {
                    name: 'category_name',
                    title: 'Category',
                    sortField: 'category_name'
                },
                {
                    name: 'created_at',
                    title: 'Created At',
                    sortField: 'created_at'
                },
                'modify'],
                perPage: 5,
                data: []
            };
        },
        watch: {
            data(newVal, oldVal) {
                this.$refs.vuetable.refresh();
            }
        },

        mounted() {
            // axios.get("https://vuetable.ratiw.net/api/users").then(response => {
            axios.get("api/projectcategories").then(response => {
                this.data = response.data.data;
            });
        },

        methods: {
            onPaginationData(paginationData) {
                this.$refs.pagination.setPaginationData(paginationData);
            },
            onChangePage(page) {
                this.$refs.vuetable.changePage(page);
            },
            dataManager(sortOrder, pagination) {
                if (this.data.length < 1) return;

                let local = this.data;

                // sortOrder can be empty, so we have to check for that as well
                if (sortOrder.length > 0) {
                    console.log("orderBy:", sortOrder[0].sortField, sortOrder[0].direction);
                    local = _.orderBy(
                        local,
                        sortOrder[0].sortField,
                        sortOrder[0].direction
                    );
                }

                pagination = this.$refs.vuetable.makePagination(
                    local.length,
                    this.perPage
                );
                console.log('pagination:', pagination)
                let from = pagination.from - 1;
                let to = from + this.perPage;

                return {
                    pagination: pagination,
                    data: _.slice(local, from, to)
                };  
            },
            onActionClicked(action, data) {
                console.log("slot actions: on-click", data.name);
            },
            openModal(user) {
                if(!user) {
                    this.editmode = false;
                    this.form.reset();
                    this.form.type = "";
                }
                else {
                    this.editmode = true;
                    this.form.fill(user);
                }
                $('#userModal').modal('show');
                this.$validator.reset();
            },
        }         
};
</script>

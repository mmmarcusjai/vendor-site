<template>
    <div class="container">
        <div class="row mt-5" v-if="$gate.isSadminOrAdmin()">
          <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Project Category Table</h3>

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
                <div class="card-footer" v-if="this.data.length > this.perPage">
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
                            <div class="form-group">
                                <textarea v-model="form.description" name="description" id="description"
                                placeholder="Short description for Category (Optional)"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('description') }"></textarea>
                                <has-error :form="form" field="description"></has-error>
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
                    id: '',
                    category_name : '',
                    description: '',
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
                {
                    name: 'modify',
                    title: 'Modify',
                }],
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
            console.log('component mounted');
        },
        methods: {
            loadCategory() {
                if(this.$gate.isSadminOrAdmin()) {
                    axios.get("api/projectcategories").then(response => {
                    // axios.get("https://vuetable.ratiw.net/api/users").then(response => {
                        this.data = response.data.data;
                    });
                }
            },
            onPaginationData(paginationData) {
                if(this.data.length > this.perPage) {
                    this.$refs.pagination.setPaginationData(paginationData);
                }
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
            checkForm() {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        if(!this.editmode) {
                            this.createCategory();
                        } else {
                            this.updateCategory();
                        }   
                        return;
                    }
                });
            },
            createCategory() {
                this.$Progress.start();
                this.form.post('api/projectcategories')
                .then((response) => {
                    console.log(response);
                    $('#userModal').modal('hide');
                    Fire.$emit('catModified');
                    Toast.fire({
                        type: 'success',
                        title: 'User create successfully'
                    });
                    this.$Progress.finish();
                })
                .catch((error) => {
                    console.log(error);
                });
            },
            updateCategory() {
                this.$Progress.start();
                this.form.put('api/projectcategories/'+this.form.id)
                .then(() => {
                    // success
                    $('#userModal').modal('hide');
                     Swal.fire(
                        'Updated!',
                        'Information has been updated.',
                        'success'
                    )
                    this.$Progress.finish();
                    Fire.$emit('catModified');
                })
                .catch(() => {
                    this.$Progress.fail();
                });
            },
            deleteUser(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    // Send request
                    if (result.value) {
                        this.form.delete('api/projectcategories/'+id).then((response)=>{
                            Swal.fire('Deleted!', 'Your file has been deleted.', 'success' );
                            Fire.$emit('catModified');
                        }).catch(()=> {
                            Swal.fire("Failed!", "There was something wronge.", "warning");
                        });
                    }
                });
            }
        },
        created() {
            this.loadCategory();
            Fire.$on('catModified', () => {
                this.loadCategory();
            });
        }         
};
</script>

<style>
    .vuetable-th-gutter {
        display: none;
    }
</style>

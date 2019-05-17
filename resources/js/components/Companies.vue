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
                    <!-- <table class="table table-hover">
                        <tbody>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Registered At</th>
                                <th>Modify</th>
                            </tr>
                            <tr v-for="user in users.data" :key="user.id">
                                <td>{{ user.id }}</td>
                                <td>{{ user.name | upperCase }}</td>
                                <td>{{ user.email}}</td>
                                <td>{{ user.created_at}}</td>
                                <td>
                                    <a href="#" @click="openModal(user)">
                                        <i class="fas fa-pen blue"></i>
                                    </a>
                                    |
                                    <a href="#" @click="deleteUser(user.id)">
                                        <i class="fas fa-trash-alt red"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table> -->
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
                                <input v-model="form.name" v-validate="'required'" type="text" name="name"
                                    placeholder="Name"
                                    class="form-control" :class="{ 'is-invalid': form.errors.has('name'), 'is-invalid': errors.has('name') }">
                                <has-error :form="form" field="name"></has-error>
                                <div v-show="errors.has('name')" class="help-block invalid-feedback">{{ errors.first('name') }}</div>
                            </div>

                                <div class="form-group">
                                <input v-model="form.email" v-validate="'required|email'" type="email" name="email"
                                    placeholder="Email Address"
                                    class="form-control" :class="{ 'is-invalid': form.errors.has('email'), 'is-invalid': errors.has('email') }">
                                <has-error :form="form" field="email"></has-error>
                                <div v-show="errors.has('email')" class="help-block invalid-feedback">{{ errors.first('email') }}</div>
                            </div>
                            <div class="form-group">
                                <textarea v-model="form.bio" name="bio" id="bio"
                                placeholder="Short bio for user (Optional)"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('bio') }"></textarea>
                                <has-error :form="form" field="bio"></has-error>
                            </div>

                            <div class="form-group" v-if="!editmode">
                                <input v-model="form.password" v-validate="'required|min:8'" type="password" name="password" id="password" placeholder="Password"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('password'), 'is-invalid': errors.has('password') }">
                                <has-error :form="form" field="password"></has-error>
                                <div v-show="errors.has('password')" class="help-block invalid-feedback">{{ errors.first('password') }}</div>
                            </div>
                            <div class="form-group" v-if="editmode">
                                <input v-model="form.password" v-validate="'min:8'" type="password" name="password" id="password" placeholder="Password"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('password')}">
                                <has-error :form="form" field="password"></has-error>
                                <div v-show="errors.has('password')" class="help-block invalid-feedback">{{ errors.first('password') }}</div>
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
                users: {},
                form: new Form({
                    id:'',
                    name : '',
                    email: '',
                    password: '',
                    type: '',
                    bio: '',
                    photo: ''
                }),
                fields: [{
                    name: 'id',
                    title: 'ID',
                    sortField: 'id'
                },
                {
                    name: 'name',
                    title: 'Name',
                    sortField: 'name'
                },
                {
                    name: 'email',
                    title: 'Email',
                    sortField: 'email'
                },
                {
                    name: 'created_at',
                    title: 'Registered At',
                    sortField: 'created_at'
                },
                {
                    name: 'modify',
                    title: 'Modify',
                }],
                perPage: 5,
                data: []
            }
        },
        watch: {
            data(newVal, oldVal) {
                this.$refs.vuetable.refresh();
            }
        },
        methods: {
            loadUsers() {
                if(this.$gate.isSadminOrAdmin()) {
                    axios.get("api/companies").then(response => {
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
                            this.createUser();
                        } else {
                            this.updateUser();
                        }   
                        return;
                    }
                });
            },
            updateUser() {
                this.$Progress.start();
                this.form.put('api/user/'+this.form.id)
                .then(() => {
                    // success
                    $('#userModal').modal('hide');
                     Swal.fire(
                        'Updated!',
                        'Information has been updated.',
                        'success'
                    )
                    this.$Progress.finish();
                    Fire.$emit('userModified');
                })
                .catch(() => {
                    this.$Progress.fail();
                });
            },
            createUser() {
                this.$Progress.start();
                this.form.post('api/user')
                .then((response) => {
                    console.log(response);
                    $('#userModal').modal('hide');
                    Fire.$emit('userModified');
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
            deleteUser(id) {
                console.log(id);
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
                        this.form.delete('api/user/'+id).then((response)=>{
                            Swal.fire('Deleted!', 'Your file has been deleted.', 'success' );
                            Fire.$emit('userModified');
                        }).catch(()=> {
                            Swal.fire("Failed!", "There was something wronge.", "warning");
                        });
                    }
                })
            }
        },
        created() {
            this.loadUsers();
            Fire.$on('userModified', () => {
                this.loadUsers();
            });
        }
    }
</script>

<template>
    <div class="container">
        <div class="row mt-5" v-if="$gate.isSadmin()">
          <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Users Table</h3>

                    <div class="card-tools">
                        <button class="btn btn-success" @click="openModal()">Add New <i class="fas fa-user-plus fa-fw"></i></button>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <tbody>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Registered At</th>
                                <th>Modify</th>
                            </tr>
                            <tr v-for="user in users.data" :key="user.id">
                                <td>{{ user.id }}</td>
                                <td>{{ user.name | upperCase }}</td>
                                <td>{{ user.email}}</td>
                                <td>{{ user.type | upperCase }}</td>
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
                    </table>
                </div>
                <!-- /.card-body -->
                <div class="card-footer" v-if="this.users.total > this.users.per_page">
                    <pagination :data="users" @pagination-change-page="getResults"></pagination>
                </div>
            </div>
            <!-- /.card -->
          </div>
        </div>

        <div v-if="!$gate.isSadmin()">
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


                            <div class="form-group">
                                <select name="type" v-model="form.type" v-validate="'required'" id="type" class="form-control" :class="{ 'is-invalid': form.errors.has('type'), 'is-invalid': errors.has('type') }">
                                    <option value="">Select User Type</option>
                                    <option value="super admin">Super Admin</option>
                                    <option value="admin">Admin</option>
                                    <option value="user">Company</option>
                                </select>
                                <has-error :form="form" field="type"></has-error>
                                <div v-show="errors.has('type')" class="help-block invalid-feedback">{{ errors.first('type') }}</div>
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
    export default {
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
            }
        },
        methods: {
            getResults(page = 1) {
                axios.get(`api/user?page=${page}`)
                    .then(response => {
                        this.users = response.data;
                });
            },
            loadUsers() {
                if(this.$gate.isSadmin()) {
                    axios.get('api/user').then(( {data} ) => (
                        this.users = data
                    ));
                }
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
                // this.$Progress.start();
                // axios.post('api/user',this.form)
                // .then((response) => {
                //     console.log(response);
                //     $('#userModal').modal('hide');
                //     Fire.$emit('userModified');
                //     Toast.fire({
                //         type: 'success',
                //         title: 'User create successfully'
                //     });
                //     this.$Progress.finish();
                // })
                // .catch((error) => {
                //     console.log(error.response);
                // });
                
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

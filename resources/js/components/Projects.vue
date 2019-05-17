<template>
    <div class="container">
        <div class="row mt-5" v-if="$gate.isSadminOrAdmin()">
          <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Project Brief Table</h3>

                    <div class="card-tools">
                        <button class="btn btn-success" @click="openModal()">Add Project <i class="fas fa-user-plus fa-fw"></i></button>
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
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" v-show="!editmode" id="addNewLabel">Add Project</h5>
                        <h5 class="modal-title" v-show="editmode" id="addNewLabel">Update Project Info</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="checkForm()" @keydown="form.onKeydown($event)">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Project: </label>
                                <input v-model="form.project_name" v-validate="'required'" type="text" name="project_name"
                                    placeholder="Project Name"
                                    class="form-control" :class="{ 'is-invalid': form.errors.has('project_name'), 'is-invalid': errors.has('project_name') }">
                                <has-error :form="form" field="project_name"></has-error>
                                <div v-show="errors.has('project_name')" class="help-block invalid-feedback">{{ errors.first('project_name') }}</div>
                            </div>
                            <div class="form-group">
                                <label>Description: </label>
                                <textarea v-model="form.description" name="description" id="description"
                                placeholder="Short description for Project (Optional)"
                                class="form-control" :class="{ 'is-invalid': form.errors.has('description') }"></textarea>
                                <has-error :form="form" field="description"></has-error>
                            </div>
                            <div class="form-group">
                                <label>Category: </label>
                                <select name="category_id" v-model="form.category_id" v-validate="'required'" id="category_id" class="form-control" :class="{ 'is-invalid': form.errors.has('category_id'), 'is-invalid': errors.has('category_id') }">
                                    <option value="">Select Category</option>
                                    <option :key="option.id" v-for="option in this.categories" v-bind:value="option.id">{{ option.category_name }}</option>
                                </select>
                                <has-error :form="form" field="category_id"></has-error>
                                <div v-show="errors.has('category_id')" class="help-block invalid-feedback">{{ errors.first('category_id') }}</div>
                            </div>
                            <div class="form-group">
                                <label>Image Information: </label>
                                <file-upload folder="project_brief" v-on:fileToParent="getImgInfo"></file-upload>
                            </div>
                            <div class="form-group">
                                <label>Start Date: </label>
                                <datepicker v-model="form.start_date" id="startDate" required input-class="form-control input-date" name="start_date" :format="format"></datepicker>

                                <label>End Date: </label>
                                <datepicker v-model="form.end_date" id="endDate" required input-class="form-control input-date" name="end_date" :format="format"></datepicker>
                                <div v-show="this.dateErrors.has('daterange')" class="help-block invalid-feedback">{{ this.dateErrors.get('daterange') }}</div>
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
    import Datepicker from 'vuejs-datepicker';
    // import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

    export default {
        components: {
            Vuetable,
            VuetablePagination,
            Datepicker
        },
        data() {
            return {
                format: "yyyy-MM-dd",
                editmode: false,
                form: new Form({
                    id: '',
                    project_name : '',
                    description: '',
                    category_id: '',
                    img_info: [],
                    start_date: '',
                    end_date: '',
                }),
                fields: [{
                    name: 'id',
                    title: 'ID',
                    sortField: 'id'
                },
                {
                    name: 'project_name',
                    title: 'Project',
                    sortField: 'project_name'
                },
                {
                    name: 'start_date',
                    title: 'Start Date',
                    sortField: 'start_date'
                },
                {
                    name: 'end_date',
                    title: 'End Date',
                    sortField: 'end_date'
                },
                {
                    name: 'company_id',
                    title: 'Company',
                    sortField: 'company_id'
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
                categories: [],
                data: [],
                dateErrors: new Map()
            };
        },
        watch: {
            data(newVal, oldVal) {
                this.$refs.vuetable.refresh();
            }
        },
        // computed: {
        //     updateDateError(key) {
        //         return this.dateErrors.delete(key);
        //     }
        // },
        methods: {
            getImgInfo(value) {
                this.form.img_info = [...this.form.img_info, value];
            },
            loadCategory() {
                if(this.$gate.isSadminOrAdmin()) {
                    axios.get("api/projectcategories").then(response => {
                        this.categories = response.data.data;
                    });
                }
            },
            loadProjects() {
                if(this.$gate.isSadminOrAdmin()) {
                    axios.get("api/projects").then(response => {
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
                    /*
                            TO-DO
                            Check date
                    */
                    let validDate = false;
                    let startDate = document.querySelector('#startDate');
                    let endDate = document.querySelector('#endDate');
                    let errorArr = new Map();
                    // Check Date range valid
                    if(startDate.value != '' && endDate.value != '') {
                        let start = startDate.value;
                        let end = endDate.value;
                        let duration = this.$moment(end).diff(this.$moment(start), 'days');

                        validDate = (duration > 0) ? true : false;
                        console.log(`duration ::::: ${duration}`);
                        console.log(`validDate ::::: ${validDate}`);

                        if(!validDate) {
                            // Show Error message
                            // Date Range invalid
                            startDate.classList.add('is-invalid');
                            endDate.classList.add('is-invalid');
                            errorArr.set('daterange', 'Date range invalid.');
                            this.dateErrors = new Map([...this.dateErrors, ...errorArr]);
                        } else {
                            startDate.classList.remove('is-invalid');
                            endDate.classList.remove('is-invalid');
                            // updateDateError('daterange');
                            this.dateErrors.delete('daterange');
                        }
                        console.log(this.dateErrors);
                    } else {
                        document.querySelectorAll('.input-date').forEach((date) => {
                            if(date.value == '') {
                                date.classList.add('is-invalid');
                            }
                        });
                    }
                    if (result && validDate) { 
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
            this.loadProjects();
            Fire.$on('catModified', () => {
                this.loadProjects();
            });
        }         
};
</script>

<style>
    .vuetable-th-gutter {
        display: none;
    }

    #userModal {
        z-index: 10000;
    }
</style>

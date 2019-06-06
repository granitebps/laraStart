<template>
  <div class="container">
    <div class="row mt-4" v-if="$gate.isAdminOrAuthor()">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Users Table</h3>

            <div class="card-tools">
              <button class="btn btn-success" @click="newModal">
                New User
                <i class="fa fa-user-plus fa-fw"></i>
              </button>
            </div>
          </div>
          <!-- /.card-header -->
          <div class="card-body table-responsive p-0">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Registered At</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in users" :key="user.id">
                  <td>{{user.id}}</td>
                  <td>{{user.name}}</td>
                  <td>{{user.email}}</td>
                  <td>{{user.role | upperText}}</td>
                  <td>{{user.created_at | humanDate}}</td>
                  <td>
                    <a href="#" @click="editModal(user)">
                      <i class="fas fa-edit blue"></i>
                    </a>
                    |
                    <a href="#" @click="deleteUser(user.id)">
                      <i class="fas fa-trash red"></i>
                    </a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
      </div>
    </div>

    <div v-if="!$gate.isAdminOrAuthor()">
      <not-found></not-found>
    </div>

    <!-- Modal -->
    <div
      class="modal fade"
      id="addNew"
      tabindex="-1"
      role="dialog"
      aria-labelledby="addNewLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addNewLabel" v-show="!editMode">Add New User</h5>
            <h5 class="modal-title" id="addNewLabel" v-show="editMode">Update User</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <!-- Begining of form -->
          <form @submit.prevent="editMode ? updateUser() : createUser()">
            <div class="modal-body">
              <div class="form-group">
                <input
                  placeholder="Name"
                  v-model="form.name"
                  type="text"
                  name="name"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.has('name') }"
                >
                <has-error :form="form" field="name"></has-error>
              </div>
              <div class="form-group">
                <input
                  placeholder="Email"
                  v-model="form.email"
                  type="email"
                  name="email"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.has('email') }"
                >
                <has-error :form="form" field="email"></has-error>
              </div>
              <div class="form-group">
                <textarea
                  placeholder="Bio"
                  v-model="form.bio"
                  name="bio"
                  cols="30"
                  rows="10"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.has('bio') }"
                ></textarea>
                <has-error :form="form" field="bio"></has-error>
              </div>
              <div class="form-group">
                <select
                  name="role"
                  v-model="form.role"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.has('role') }"
                >
                  <option value>-- Select User Role --</option>
                  <option value="admin">Admin</option>
                  <option value="user">User</option>
                  <option value="author">Author</option>
                </select>
                <has-error :form="form" field="role"></has-error>
              </div>
              <div class="form-group">
                <input
                  placeholder="Password"
                  v-model="form.password"
                  type="password"
                  name="password"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.has('password') }"
                >
                <has-error :form="form" field="password"></has-error>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
              <button v-show="editMode" type="submit" class="btn btn-success">Edit User</button>
              <button v-show="!editMode" type="submit" class="btn btn-primary">Create User</button>
            </div>
          </form>
          <!-- End of form -->
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      editMode: false,
      users: {},
      form: new Form({
        id: "",
        name: "",
        email: "",
        password: "",
        role: "",
        bio: "",
        photo: ""
      })
    };
  },
  methods: {
    // Modal for create user
    newModal() {
      this.editMode = false;
      this.form.reset();
      $("#addNew").modal("show");
    },

    // Modal for edit user
    editModal(user) {
      this.editMode = true;
      this.form.reset();
      $("#addNew").modal("show");
      this.form.fill(user);
      console.log(this.form);
    },

    // function to retrieve user
    loadUser() {
      if (this.$gate.isAdminOrAuthor()) {
        axios
          .get("api/user")
          .then(({ data }) => {
            this.users = data.data;
          })
          .catch(() => {
            swal.fire("Failed!", "There was something wrong", "warning");
          });
      }
    },

    // function to create user
    createUser() {
      this.$Progress.start();
      axios
        .post("api/user", this.form)
        .then(() => {
          $("#addNew").modal("hide");
          toast.fire({
            type: "success",
            title: "User created successfully"
          });
          this.$Progress.finish();
          this.loadUser();
        })
        .catch(err => {
          console.log(err);
        });
    },

    // function to edit user
    updateUser() {
      this.$Progress.start();
      // console.log("edit");
      axios
        .put("api/user/" + this.form.id, this.form)
        .then(() => {
          $("#addNew").modal("hide");
          swal.fire("Updated!", "Your user has been updated.", "success");
          this.loadUser();
          this.$Progress.finish();
        })
        .catch(err => {
          this.$Progress.fail();
          console.log(err);
        });
    },

    // function to delete user
    deleteUser(id) {
      swal
        .fire({
          title: "Are you sure?",
          text: "You won't be able to revert this!",
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Yes, delete it!"
        })
        .then(result => {
          if (result.value) {
            // Delete user
            axios
              .delete("api/user/" + id)
              .then(() => {
                swal.fire("Deleted!", "Your user has been deleted.", "success");
                this.loadUser();
              })
              .catch(err => {
                console.log(err);
                swal.fire("Failed!", "There was something wrong", "warning");
              });
          }
        });
    }
  },
  created() {
    this.loadUser();
  }
};
</script>

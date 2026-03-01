<template>
  <div class="container mt-4">
    <h2 class="mb-3">Employee List</h2>

    <div class="mb-3 d-flex justify-content-start">
      <button class="btn btn-primary" @click="openAddModal">Add+</button>
    </div>

    <table class="table table-bordered table-striped">
      <thead class="table-primary">
        <tr>
          <th>รูปภาพ</th>
          <th>รหัส</th>
          <th>ชื่อ</th>
          <th>นามสกุล</th>
          <th>ที่อยู่</th>
          <th>เบอร์โทรศัพท์</th>
          <th>แก้ไข/ลบ</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="employee in employees" :key="employee.emp_id">
          <td>
            <img
              v-if="employee.image"
              :src="
                'http://localhost/project_crud/php_api/uploads/' +
                employee.image
              "
              width="100"
            />
          </td>
          <td>{{ employee.emp_id }}</td>
          <td>{{ employee.first_name }}</td>
          <td>{{ employee.last_name }}</td>
          <td>{{ employee.address }}</td>
          <td>{{ employee.phone }}</td>
          <td>
            <button
              class="btn btn-warning btn-sm me-2"
              @click="openEditModal(employee)"
            >
              แก้ไข
            </button>
            <button
              class="btn btn-danger btn-sm"
              @click="deleteEmployees(employee.emp_id)"
            >
              ลบ
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="loading" class="text-center"><p>กำลังโหลดข้อมูล...</p></div>
    <div v-if="error" class="alert alert-danger">{{ error }}</div>

    <!-- Modal ใช้ทั้งเพิ่ม / แก้ไข -->
    <div class="modal fade" id="editModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered modal-sm" style="max-width: 400px;">
        <div class="modal-content border-0 shadow-lg">
          
          <div class="modal-body p-4">
            <h4 class="text-center mb-4">
              {{ isEditMode ? "Edit Employee" : "Add Employee" }}
            </h4>
            
            <form @submit.prevent="saveEmployees">
              <div class="mb-3">
                <input v-model="editForm.first_name" type="text" class="form-control" placeholder="First Name" required />
              </div>
              <div class="mb-3">
                <input v-model="editForm.last_name" type="text" class="form-control" placeholder="Last Name" required />
              </div>
              <div class="mb-3">
                <input v-model="editForm.address" type="text" class="form-control" placeholder="Address" required />
              </div>
              <div class="mb-3">
                <input v-model="editForm.phone" type="text" class="form-control" placeholder="Phone" required />
              </div>
              
              <div class="mb-4">
                <input type="file" @change="handleFileUpload" class="form-control form-control-sm" :required="!isEditMode" />
                <div v-if="isEditMode && editForm.image" class="mt-2 text-center">
                  <p class="mb-1 text-start" style="font-size: 0.8rem;">รูปเดิม:</p>
                  <img :src="'http://localhost/project_crud/php_api/uploads/' + editForm.image" width="80" class="rounded" />
                </div>
              </div>

              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary fw-bold">
                  {{ isEditMode ? "Update" : "Add" }}
                </button>
                <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">
                  Cancel
                </button>
              </div>
            </form>
            
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from "vue";

export default {
  name: "Employees",
  setup() {
    const employees = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const isEditMode = ref(false); // ✅ เช็คโหมด
    const editForm = ref({
      emp_id: null,
      first_name: "",
      last_name: "",
      address: "",
      phone: "",
      image: "",
    });
    const newImageFile = ref(null);
    let modalInstance = null;

    // โหลดข้อมูลสินค้า
    const fetchEmployees = async () => {
      try {
        const res = await fetch(
          "http://localhost/project_crud/php_api/profile_db.php"
        );
        const data = await res.json();
        employees.value = data.success ? data.data : [];
      } catch (err) {
        error.value = err.message;
      } finally {
        loading.value = false;
      }
    };

    // เปิด Modal สำหรับเพิ่มสินค้า
    const openAddModal = () => {
      isEditMode.value = false;
      editForm.value = {
        emp_id: null,
        first_name: "",
        last_name: "",
        address: "",
        phone: "",
        image: "",
      };
      newImageFile.value = null;

      const modalEl = document.getElementById("editModal");
      modalInstance = new window.bootstrap.Modal(modalEl);
      modalInstance.show();

      // ✅ รีเซ็ตค่า input file ให้ไม่แสดงชื่อไฟล์ค้าง
      const fileInput = modalEl.querySelector('input[type="file"]');
      if (fileInput) fileInput.value = "";
    };

    // เปิด Modal สำหรับแก้ไขสินค้า
    const openEditModal = (employees) => {
      isEditMode.value = true;
      editForm.value = { ...employees };
      newImageFile.value = null;
      const modalEl = document.getElementById("editModal");
      modalInstance = new window.bootstrap.Modal(modalEl);
      modalInstance.show();
    };

    const handleFileUpload = (event) => {
      newImageFile.value = event.target.files[0];
    };

    // ✅ ใช้ฟังก์ชันเดียวในการเพิ่ม / แก้ไข
    const saveEmployees = async () => {
      const formData = new FormData();
      formData.append("action", isEditMode.value ? "update" : "add");
      if (isEditMode.value) formData.append("emp_id", editForm.value.emp_id);
      formData.append("first_name", editForm.value.first_name);
      formData.append("last_name", editForm.value.last_name);
      formData.append("address", editForm.value.address);
      formData.append("phone", editForm.value.phone);
      if (newImageFile.value) formData.append("image", newImageFile.value);

      try {
        const res = await fetch(
          "http://localhost/project_crud/php_api/profile_db.php",
          {
            method: "POST",
            body: formData,
          }
        );
        const result = await res.json();
        if (result.message) {
          alert(result.message);
          fetchEmployees();
          modalInstance.hide();
        } else if (result.error) {
          alert(result.error);
        }
      } catch (err) {
        alert(err.message);
      }
    };

    // ลบสินค้า
    const deleteEmployees = async (id) => {
      if (!confirm("Are you sure you want to delete this Employee?")) return;

      const formData = new FormData();
      formData.append("action", "delete");
      formData.append("emp_id", id);

      try {
        const res = await fetch(
          "http://localhost/project_crud/php_api/profile_db.php",
          {
            method: "POST",
            body: formData,
          }
        );
        const result = await res.json();
        if (result.message) {
          alert(result.message);
          employees.value = employees.value.filter((e) => e.emp_id !== id);
        } else if (result.error) {
          alert(result.error);
        }
      } catch (err) {
        alert(err.message);
      }
    };

    onMounted(fetchEmployees);

    return {
      employees,
      loading,
      error,
      editForm,
      isEditMode,
      openAddModal,
      openEditModal,
      handleFileUpload,
      saveEmployees,
      deleteEmployees,
    };
  },
};
</script>

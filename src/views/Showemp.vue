<template>
  <div class="container mt-4">
    <h3 class="mb-4 text-center text-secondary">Employee List</h3>

    <div v-if="loading" class="text-center"><p>กำลังโหลดข้อมูล...</p></div>
    <div v-if="error" class="alert alert-danger">{{ error }}</div>

    <div class="row">
      <div
        class="col-12 col-md-4 mb-4"
        v-for="employee in employees"
        :key="employee.emp_id"
      >
        <div
          class="card h-100 shadow-sm"
          style="border-radius: 8px; overflow: hidden"
        >
          <img
            v-if="employee.image"
            :src="
              'http://localhost/project_crud/php_api/uploads/' + employee.image
            "
            class="card-img-top"
            alt="Employee Image"
            style="height: 250px; object-fit: cover"
          />
          <div
            v-else
            class="bg-light d-flex align-items-center justify-content-center"
            style="height: 250px"
          >
            <span class="text-muted">No Image</span>
          </div>

          <div class="card-body text-center">
            <h6
              class="card-title fw-bold text-dark mb-2"
              style="font-size: 1.1rem"
            >
              {{ employee.first_name }} {{ employee.last_name }}
            </h6>
            <p class="card-text text-primary fw-bold" style="font-size: 0.9rem">
              Phone: {{ employee.phone }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from "vue";

export default {
  name: "ShowEmployee",
  setup() {
    const employees = ref([]);
    const loading = ref(true);
    const error = ref(null);

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

    onMounted(fetchEmployees);

    return { employees, loading, error };
  },
};
</script>

<template>
	<!-- Manage Academies Modal -->
	<div v-if="showManageAcademies" class="modal-overlay" @click="showManageAcademies = false">
		<div class="modal-content" @click.stop>
			<div class="modal-header">
				<h2>Academy Management</h2>
				<button @click="showManageAcademies = false" class="close-button">×</button>
			</div>
			<ManageAcademies />
		</div>
	</div>

	<!-- Login Screen -->
	<div v-if="!isAuthenticated" class="login-container">
		<div class="login-card">
			<div class="login-header">
				<h1>Admin Login</h1>
				<p>Sign in to your account</p>
			</div>
			
			<form @submit.prevent="handleLogin" class="login-form">
				<div class="form-group">
					<label for="email">Email</label>
					<input
						id="email"
						type="email"
						v-model="loginForm.email"
						:class="{ 'error': errors.email }"
						placeholder="Enter your email"
						required
					/>
					<span v-if="errors.email" class="error-message">{{ errors.email }}</span>
				</div>

				<div class="form-group">
					<label for="password">Password</label>
					<input
						id="password"
						type="password"
						v-model="loginForm.password"
						:class="{ 'error': errors.password }"
						placeholder="Enter your password"
						required
					/>
					<span v-if="errors.password" class="error-message">{{ errors.password }}</span>
				</div>

				<div class="form-group checkbox-group">
					<label>
						<input type="checkbox" v-model="loginForm.remember">
						Remember me
					</label>
				</div>

				<button 
					type="submit" 
					class="login-button"
					:disabled="isLoading"
				>
					<span v-if="isLoading">Signing in...</span>
					<span v-else>Sign In</span>
				</button>

				<div v-if="errors.general" class="error-message general-error">
					{{ errors.general }}
				</div>
			</form>
		</div>
	</div>

	<!-- Admin Panel (shown after login) -->
	<main v-else class="admin-panel">
		<header class="admin-header">
			<div class="header-left">
				<h1>{{ getAdminPanelTitle() }}</h1>
				<span class="role-badge" :class="getRoleBadgeClass()">{{ getUserRoleName() }}</span>
			</div>
			<div class="user-info">
				<span>Welcome, {{ user?.name || user?.email }}</span>
				<button @click="handleLogout" class="logout-button">Logout</button>
			</div>
		</header>
		
		<div class="admin-content">
			<div class="welcome-message">
				<h2>{{ getWelcomeMessage() }}</h2>
				<p>{{ getDescriptionMessage() }}</p>
			</div>
			
			<!-- Super Admin specific content -->
			<div v-if="user?.role_id === 1" class="super-admin-section">
				<div class="admin-cards">
					<div class="admin-card">
						<h3>🏢 Academy Management</h3>
						<p>Manage academy registrations and approvals</p>
						<button class="card-button" @click="showManageAcademies = true">Manage Academies</button>
					</div>
					<div class="admin-card">
						<h3>👥 User Management</h3>
						<p>Oversee all users in the system</p>
						<button class="card-button">Manage Users</button>
					</div>
					<div class="admin-card">
						<h3>📊 System Reports</h3>
						<p>View system-wide analytics and reports</p>
						<button class="card-button">View Reports</button>
					</div>
				</div>
			</div>
			
			<!-- Academy Admin specific content -->
			<div v-if="user?.role_id === 2" class="academy-admin-section">
				<div class="admin-cards">
					<div class="admin-card">
						<h3>👨‍🏫 Teacher Management</h3>
						<p>Manage teachers and their requests</p>
						<button class="card-button">Manage Teachers</button>
					</div>
					<div class="admin-card">
						<h3>👨‍🎓 Student Management</h3>
						<p>Handle student enrollments and requests</p>
						<button class="card-button">Manage Students</button>
					</div>
					<div class="admin-card">
						<h3>📚 Course Management</h3>
						<p>Create and manage courses</p>
						<button class="card-button">Manage Courses</button>
					</div>
					<div class="admin-card">
						<h3>📝 Exam Management</h3>
						<p>Create and manage course exams</p>
						<button class="card-button">Manage Exams</button>
					</div>
				</div>
			</div>
			
			<div class="user-details">
				<h3>User Information:</h3>
				<div class="user-info-card">
					<p><strong>Name:</strong> {{ user?.name || 'N/A' }}</p>
					<p><strong>Email:</strong> {{ user?.email }}</p>
					<p><strong>Role:</strong> {{ getUserRoleName() }}</p>
					<p><strong>Role ID:</strong> {{ user?.role_id }}</p>
					<details>
						<summary>Full User Data</summary>
						<pre>{{ JSON.stringify(user, null, 2) }}</pre>
					</details>
				</div>
			</div>
		</div>
	</main>
</template>



<script setup>
import { ref, reactive, onMounted } from 'vue'
import ManageAcademies from './components/ManageAcademies.vue'

// State
const isAuthenticated = ref(false)
const isLoading = ref(false)
const user = ref(null)
const showManageAcademies = ref(false)

// Form data
const loginForm = reactive({
	email: '',
	password: '',
	remember: false
})

// Errors
const errors = reactive({
	email: '',
	password: '',
	general: ''
})

// Clear errors
const clearErrors = () => {
	errors.email = ''
	errors.password = ''
	errors.general = ''
}

// New function to safely extract user data from different API response formats
function extractUserDataFromResponse(responseData) {
	console.log('Extracting user data from response:', responseData)
	
	// Try different possible structures
	if (responseData.user) {
		return responseData.user
	} else if (responseData.data && responseData.data.user) {
		return responseData.data.user
	} else if (responseData.role_id !== undefined) {
		// User data might be directly in the response
		return responseData
	}
	
	return null
}

// New function to safely get user role ID
function getUserRoleIdSafely(userData) {
	if (!userData) {
		console.error('No user data provided to getUserRoleIdSafely')
		return null
	}
	
	// Check different possible property names
	if (userData.role_id !== undefined) {
		return userData.role_id
	} else if (userData.roleId !== undefined) {
		return userData.roleId
	} else if (userData.role !== undefined) {
		return userData.role
	} else if (userData.user_type !== undefined) {
		return userData.user_type
	}
	
	console.error('Could not find role_id in user data:', userData)
	return null
}

function fetchUserDetails(token) {
    return fetch('/api/user/details', {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('User Details:', data);
        return data;
    })
    .catch(error => {
        console.error('Error fetching user details:', error);
    });
}

// Check if user is already authenticated
const checkAuth = async () => {
	const token = localStorage.getItem('auth_token')
	
	if (!token) {
		isAuthenticated.value = false
		return
	}

	try {
		// Verify token is still valid
		const response = await fetch('/api/user', {
			headers: {
				'Authorization': `Bearer ${token}`,
				'Content-Type': 'application/json',
				'Accept': 'application/json'
			}
		})

		if (response.ok) {
			const userData = await response.json()
			
			// Check if user has admin access
			if (userData.role_id === 1 || userData.role_id === 2) {
				user.value = userData
				isAuthenticated.value = true
			} else {
				// User doesn't have admin access, clear token
				localStorage.removeItem('auth_token')
				isAuthenticated.value = false
			}
		} else {
			// Token is invalid
			localStorage.removeItem('auth_token')
			isAuthenticated.value = false
		}
	} catch (error) {
		console.error('Auth check failed:', error)
		localStorage.removeItem('auth_token')
		isAuthenticated.value = false
	}
}

// Handle login
const handleLogin = async () => {
	clearErrors()
	isLoading.value = true

	// Basic validation
	if (!loginForm.email) {
		errors.email = 'Email is required'
		isLoading.value = false
		return
	}
	
	if (!loginForm.password) {
		errors.password = 'Password is required'
		isLoading.value = false
		return
	}

	try {
		const response = await fetch('/api/login', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'Accept': 'application/json'
			},
			body: JSON.stringify({
				email: loginForm.email,
				password: loginForm.password,
				remember: loginForm.remember
			})
		})

		const data = await response.json()
		console.log('Login response data:', data) // Debug log

		if (response.ok) {
			// Login successful - handle different response structures
			if (data.token) {
				localStorage.setItem('auth_token', data.token)
			} else if (data.access_token) {
				localStorage.setItem('auth_token', data.access_token)
			}

			// Handle different user data structures
			let userData = null;
			if (data.user) {
				userData = data.user
			} else if (data.data && data.data.user) {
				userData = data.data.user
			} else if (data.role_id) {
				// User data might be directly in the response
				userData = data
			}

			if (!userData) {
				console.error('No user data found in response:', data)
				errors.general = 'Invalid response format. Please contact administrator.'
				return
			}

			user.value = userData
			
			// Check user role and redirect accordingly
			const roleId = userData.role_id
			console.log('User role_id:', roleId) // Debug log
			
			if (roleId === undefined || roleId === null) {
				console.error('role_id is missing from user data:', userData)
				errors.general = 'User role information is missing. Please contact administrator.'
				return
			}
			
			// Only allow Super Admin (role_id: 1) and Academy Admin (role_id: 2) to access this admin panel
			if (roleId === 1 || roleId === 2) {
				isAuthenticated.value = true
				
				// Clear form
				loginForm.email = ''
				loginForm.password = ''
				loginForm.remember = false
			} else {
				// User doesn't have admin access
				localStorage.removeItem('auth_token')
				errors.general = 'Access denied. This panel is only for Super Admins and Academy Administrators.'
				return
			}
		} else {
			// Login failed
			console.log('Login failed with status:', response.status, 'Data:', data)
			if (data.errors) {
				// Validation errors
				if (data.errors.email) {
					errors.email = data.errors.email[0]
				}
				if (data.errors.password) {
					errors.password = data.errors.password[0]
				}
			} else if (data.message) {
				// General error message
				errors.general = data.message
			} else {
				errors.general = 'Login failed. Please check your credentials.'
			}
		}
	} catch (error) {
		console.error('Login error:', error)
		errors.general = 'Network error. Please try again.'
	} finally {
		isLoading.value = false
	}
}

// Handle logout
const handleLogout = async () => {
	try {
		const token = localStorage.getItem('auth_token')
		
		if (token) {
			// Call logout API endpoint if available
			await fetch('/api/logout', {
				method: 'POST',
				headers: {
					'Authorization': `Bearer ${token}`,
					'Content-Type': 'application/json',
					'Accept': 'application/json'
				}
			})
		}
	} catch (error) {
		console.error('Logout API call failed:', error)
	} finally {
		// Always clear local state
		localStorage.removeItem('auth_token')
		user.value = null
		isAuthenticated.value = false
	}
}

// Helper functions for UI
const getUserRoleName = () => {
	const roles = {
		1: 'Super Admin',
		2: 'Academy Administrator',
		3: 'Teacher',
		4: 'Student'
	}
	return roles[user.value?.role_id] || 'Unknown'
}

const getAdminPanelTitle = () => {
	if (user.value?.role_id === 1) {
		return 'Super Admin Panel'
	} else if (user.value?.role_id === 2) {
		return 'Academy Admin Panel'
	}
	return 'Admin Panel'
}

const getRoleBadgeClass = () => {
	if (user.value?.role_id === 1) {
		return 'super-admin'
	} else if (user.value?.role_id === 2) {
		return 'academy-admin'
	}
	return ''
}

const getWelcomeMessage = () => {
	if (user.value?.role_id === 1) {
		return 'Welcome to the Super Admin Dashboard! 🌟'
	} else if (user.value?.role_id === 2) {
		return 'Welcome to the Academy Administration Panel! 🏫'
	}
	return 'Welcome to the Admin Panel!'
}

const getDescriptionMessage = () => {
	if (user.value?.role_id === 1) {
		return 'You have full system access. Manage academies, users, and system-wide settings.'
	} else if (user.value?.role_id === 2) {
		return 'Manage your academy\'s teachers, students, courses, and exams.'
	}
	return 'Access your administrative functions.'
}

// Initialize
onMounted(() => {
	checkAuth()
})
</script>

<style scoped>
/* Login Styles */
.login-container {
	min-height: 100vh;
	display: flex;
	align-items: center;
	justify-content: center;
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	padding: 20px;
}

.login-card {
	background: white;
	border-radius: 12px;
	box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
	padding: 40px;
	width: 100%;
	max-width: 400px;
}

.login-header {
	text-align: center;
	margin-bottom: 32px;
}

.login-header h1 {
	color: #2d3748;
	font-size: 28px;
	font-weight: 700;
	margin: 0 0 8px 0;
}

.login-header p {
	color: #718096;
	margin: 0;
	font-size: 16px;
}

.login-form {
	display: flex;
	flex-direction: column;
	gap: 20px;
}

.form-group {
	display: flex;
	flex-direction: column;
}

.form-group label {
	color: #2d3748;
	font-weight: 600;
	margin-bottom: 8px;
	font-size: 14px;
}

.form-group input {
	padding: 12px 16px;
	border: 2px solid #e2e8f0;
	border-radius: 8px;
	font-size: 16px;
	transition: border-color 0.2s ease;
}

.form-group input:focus {
	outline: none;
	border-color: #667eea;
}

.form-group input.error {
	border-color: #e53e3e;
}

.checkbox-group {
	flex-direction: row;
	align-items: center;
}

.checkbox-group label {
	display: flex;
	align-items: center;
	gap: 8px;
	margin: 0;
	font-weight: normal;
	cursor: pointer;
}

.checkbox-group input[type="checkbox"] {
	width: auto;
	margin: 0;
}

.login-button {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
	border: none;
	padding: 14px 24px;
	border-radius: 8px;
	font-size: 16px;
	font-weight: 600;
	cursor: pointer;
	transition: opacity 0.2s ease;
}

.login-button:hover:not(:disabled) {
	opacity: 0.9;
}

.login-button:disabled {
	opacity: 0.6;
	cursor: not-allowed;
}

.error-message {
	color: #e53e3e;
	font-size: 14px;
	margin-top: 4px;
}

.general-error {
	text-align: center;
	margin-top: 4px;
}

/* Admin Panel Styles */
.admin-panel {
	min-height: 100vh;
	background: #f7fafc;
}

.admin-header {
	background: white;
	border-bottom: 1px solid #e2e8f0;
	padding: 20px 24px;
	display: flex;
	justify-content: space-between;
	align-items: center;
}

.header-left {
	display: flex;
	align-items: center;
	gap: 12px;
}

.admin-header h1 {
	color: #2d3748;
	font-size: 24px;
	font-weight: 700;
	margin: 0;
}

.role-badge {
	padding: 4px 12px;
	border-radius: 20px;
	font-size: 12px;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

.role-badge.super-admin {
	background: linear-gradient(135deg, #ff6b6b, #ee5a24);
	color: white;
}

.role-badge.academy-admin {
	background: linear-gradient(135deg, #4834d4, #686de0);
	color: white;
}

.user-info {
	display: flex;
	align-items: center;
	gap: 16px;
	color: #4a5568;
}

.logout-button {
	background: #e53e3e;
	color: white;
	border: none;
	padding: 8px 16px;
	border-radius: 6px;
	font-size: 14px;
	cursor: pointer;
	transition: background-color 0.2s ease;
}

.logout-button:hover {
	background: #c53030;
}

.admin-content {
	padding: 24px;
}

.welcome-message {
	text-align: center;
	margin-bottom: 32px;
}

.welcome-message h2 {
	color: #2d3748;
	font-size: 28px;
	margin: 0 0 8px 0;
}

.welcome-message p {
	color: #718096;
	font-size: 16px;
	margin: 0;
}

.admin-cards {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
	gap: 20px;
	margin-bottom: 32px;
}

.admin-card {
	background: white;
	border-radius: 12px;
	padding: 24px;
	border: 1px solid #e2e8f0;
	box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
	transition: all 0.2s ease;
}

.admin-card:hover {
	box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
	transform: translateY(-2px);
}

.admin-card h3 {
	color: #2d3748;
	font-size: 18px;
	margin: 0 0 8px 0;
}

.admin-card p {
	color: #718096;
	margin: 0 0 16px 0;
	font-size: 14px;
}

.card-button {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
	border: none;
	padding: 10px 20px;
	border-radius: 6px;
	font-size: 14px;
	font-weight: 600;
	cursor: pointer;
	transition: opacity 0.2s ease;
	width: 100%;
}

.card-button:hover {
	opacity: 0.9;
}

.user-details {
	background: white;
	border-radius: 8px;
	padding: 20px;
	margin-top: 20px;
	border: 1px solid #e2e8f0;
}

.user-details h3 {
	color: #2d3748;
	margin: 0 0 16px 0;
}

.user-info-card {
	background: #f7fafc;
	padding: 16px;
	border-radius: 8px;
	margin-bottom: 16px;
}

.user-info-card p {
	margin: 8px 0;
	color: #2d3748;
}

.user-details details {
	margin-top: 16px;
}

.user-details summary {
	cursor: pointer;
	color: #667eea;
	font-weight: 600;
	margin-bottom: 8px;
}

.user-details pre {
	background: #f7fafc;
	padding: 16px;
	border-radius: 6px;
	overflow-x: auto;
	font-size: 12px;
	color: #2d3748;
	margin: 0;
}

/* Modal Styles */
.modal-overlay {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background: rgba(0, 0, 0, 0.6);
	display: flex;
	align-items: center;
	justify-content: center;
	z-index: 1000;
	padding: 20px;
}

.modal-content {
	background: white;
	border-radius: 12px;
	width: 100%;
	max-width: 1200px;
	max-height: 90vh;
	overflow-y: auto;
	box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
	display: flex;
	flex-direction: column;
}

.modal-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 20px 24px;
	border-bottom: 1px solid #e2e8f0;
	background: #f7fafc;
}

.modal-header h2 {
	color: #2d3748;
	font-size: 24px;
	font-weight: 700;
	margin: 0;
}

.close-button {
	background: none;
	border: none;
	font-size: 28px;
	color: #718096;
	cursor: pointer;
	padding: 0;
	width: 32px;
	height: 32px;
	display: flex;
	align-items: center;
	justify-content: center;
	border-radius: 50%;
	transition: background-color 0.2s ease;
}

.close-button:hover {
	background: #e2e8f0;
	color: #2d3748;
}

/* Responsive Design */
@media (max-width: 768px) {
	.admin-cards {
		grid-template-columns: 1fr;
	}
	
	.admin-header {
		flex-direction: column;
		gap: 16px;
		align-items: flex-start;
	}
	
	.header-left {
		flex-direction: column;
		align-items: flex-start;
		gap: 8px;
	}
	
	.user-info {
		width: 100%;
		justify-content: space-between;
	}
}

@media (max-width: 480px) {
	.login-container {
		padding: 16px;
	}
	
	.login-card {
		padding: 24px;
	}
	
	.welcome-message h2 {
		font-size: 24px;
	}
	
	.admin-card {
		padding: 20px;
	}
	
	.modal-overlay {
		padding: 10px;
	}
	
	.modal-content {
		max-height: 95vh;
	}
	
	.modal-header {
		padding: 16px 20px;
	}
	
	.modal-header h2 {
		font-size: 20px;
	}
}
</style>

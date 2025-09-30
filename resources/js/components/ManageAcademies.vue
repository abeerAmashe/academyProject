<template>
	<div class="manage-academies">
		<div class="header">
			<h2>Manage Academies</h2>
			<p>View and manage all academy-related information</p>
		</div>

		<div class="table-tabs">
			<button 
				v-for="table in tables" 
				:key="table.key"
				:class="['tab-button', { active: activeTab === table.key }]"
				@click="activeTab = table.key"
			>
				{{ table.name }}
			</button>
		</div>

		<div class="table-container">
			<div v-if="loading" class="loading">
				Loading {{ activeTableName }}...
			</div>

			<div v-else-if="error" class="error">
				{{ error }}
			</div>

			<div v-else class="data-table">
				<div class="table-header">
					<h3>{{ activeTableName }} ({{ currentData.length }} records)</h3>
					<button @click="refreshData" class="refresh-btn">
						🔄 Refresh
					</button>
				</div>

				<div v-if="currentData.length === 0" class="no-data">
					No data available for {{ activeTableName }}
				</div>

				<table v-else class="data-grid">
					<thead>
						<tr>
							<th v-for="column in currentColumns" :key="column">
								{{ formatColumnName(column) }}
							</th>
							<th v-if="activeTab === 'pending'" class="action-column">Decision</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="row in currentData" :key="row.id">
							<td v-for="column in currentColumns" :key="column">
								{{ formatCellValue(row[column], column) }}
							</td>
							<td v-if="activeTab === 'pending'" class="action-cell">
								<div class="action-buttons">
									<button 
										@click="acceptRequest(row.id)" 
										:disabled="processingRequest"
										class="accept-btn"
										title="Accept Request"
									>
										✓ Accept
									</button>
									<button 
										@click="rejectRequest(row.id)" 
										:disabled="processingRequest"
										class="reject-btn"
										title="Reject Request"
									>
										✗ Reject
									</button>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'

// State
const activeTab = ref('academies')
const loading = ref(false)
const error = ref('')
const processingRequest = ref(false)

// Data for each table
const academiesData = ref([])
const administratorsData = ref([])
const notificationsData = ref([])
const pendingData = ref([])
const studentsData = ref([])
const teachersData = ref([])

// Table configurations
const tables = [
	{ key: 'academies', name: 'Academies', endpoint: '/api/super-admin/academies' },
	{ key: 'administrators', name: 'Academy Administrators', endpoint: '/api/super-admin/academy-administrators' },
	{ key: 'notifications', name: 'Academy Notifications', endpoint: '/api/super-admin/academy-notifications' },
	{ key: 'pending', name: 'Academy Pending', endpoint: '/api/super-admin/academy-pending' },
	{ key: 'students', name: 'Academy Students', endpoint: '/api/super-admin/academy-students' },
	{ key: 'teachers', name: 'Academy Teachers', endpoint: '/api/super-admin/academy-teachers' }
]

// Computed properties
const currentData = computed(() => {
	switch (activeTab.value) {
		case 'academies': return academiesData.value
		case 'administrators': return administratorsData.value
		case 'notifications': return notificationsData.value
		case 'pending': return pendingData.value
		case 'students': return studentsData.value
		case 'teachers': return teachersData.value
		default: return []
	}
})

const activeTableName = computed(() => {
	const table = tables.find(t => t.key === activeTab.value)
	return table ? table.name : ''
})

const currentColumns = computed(() => {
	if (currentData.value.length === 0) return []
	return Object.keys(currentData.value[0])
})

// Methods
const formatColumnName = (column) => {
	return column.split('_').map(word => 
		word.charAt(0).toUpperCase() + word.slice(1)
	).join(' ')
}

const formatCellValue = (value, column) => {
	if (value === null || value === undefined) return 'N/A'
	
	// Format timestamps
	if (column.includes('_at') && value) {
		return new Date(value).toLocaleString()
	}
	
	// Format boolean values
	if (typeof value === 'boolean') {
		return value ? 'Yes' : 'No'
	}
	
	// Truncate long text
	if (typeof value === 'string' && value.length > 50) {
		return value.substring(0, 50) + '...'
	}
	
	return value
}

const fetchData = async (endpoint, dataRef) => {
	try {
		loading.value = true
		error.value = ''
		
		const token = localStorage.getItem('auth_token')
		const response = await fetch(endpoint, {
			headers: {
				'Authorization': `Bearer ${token}`,
				'Content-Type': 'application/json',
				'Accept': 'application/json'
			}
		})

		if (!response.ok) {
			throw new Error(`HTTP ${response.status}: ${response.statusText}`)
		}

		const result = await response.json()
		
		// Handle different response formats
		if (Array.isArray(result)) {
			dataRef.value = result
		} else if (result.data && Array.isArray(result.data)) {
			dataRef.value = result.data
		} else if (result.academies && Array.isArray(result.academies)) {
			dataRef.value = result.academies
		} else {
			dataRef.value = []
		}

	} catch (err) {
		console.error(`Error fetching data from ${endpoint}:`, err)
		error.value = `Failed to load data: ${err.message}`
		dataRef.value = []
	} finally {
		loading.value = false
	}
}

const loadCurrentTabData = async () => {
	const table = tables.find(t => t.key === activeTab.value)
	if (!table) return

	let dataRef
	switch (activeTab.value) {
		case 'academies': dataRef = academiesData; break
		case 'administrators': dataRef = administratorsData; break
		case 'notifications': dataRef = notificationsData; break
		case 'pending': dataRef = pendingData; break
		case 'students': dataRef = studentsData; break
		case 'teachers': dataRef = teachersData; break
		default: return
	}

	await fetchData(table.endpoint, dataRef)
}

const refreshData = () => {
	loadCurrentTabData()
}

// Handle academy pending requests
const acceptRequest = async (pendingId) => {
	if (!confirm('Are you sure you want to accept this academy request?')) return
	
	try {
		processingRequest.value = true
		const token = localStorage.getItem('auth_token')
		
		const response = await fetch(`/api/super-admin/accept-academy/${pendingId}`, {
			method: 'GET',
			headers: {
				'Authorization': `Bearer ${token}`,
				'Content-Type': 'application/json',
				'Accept': 'application/json'
			}
		})

		if (!response.ok) {
			throw new Error(`HTTP ${response.status}: ${response.statusText}`)
		}

		const result = await response.json()
		
		// Show success message
		alert('Academy request accepted successfully!')
		
		// Refresh the pending data to remove the accepted request
		await loadCurrentTabData()
		
	} catch (err) {
		console.error('Error accepting academy request:', err)
		alert(`Failed to accept academy request: ${err.message}`)
	} finally {
		processingRequest.value = false
	}
}

const rejectRequest = async (pendingId) => {
	if (!confirm('Are you sure you want to reject this academy request? This action cannot be undone.')) return
	
	try {
		processingRequest.value = true
		const token = localStorage.getItem('auth_token')
		
		const response = await fetch(`/api/super-admin/reject-academy/${pendingId}`, {
			method: 'GET',
			headers: {
				'Authorization': `Bearer ${token}`,
				'Content-Type': 'application/json',
				'Accept': 'application/json'
			}
		})

		if (!response.ok) {
			throw new Error(`HTTP ${response.status}: ${response.statusText}`)
		}

		const result = await response.json()
		
		// Show success message
		alert('Academy request rejected successfully!')
		
		// Refresh the pending data to remove the rejected request
		await loadCurrentTabData()
		
	} catch (err) {
		console.error('Error rejecting academy request:', err)
		alert(`Failed to reject academy request: ${err.message}`)
	} finally {
		processingRequest.value = false
	}
}

// Load data when tab changes
watch(activeTab, () => {
	loadCurrentTabData()
})

// Load initial data
onMounted(() => {
	loadCurrentTabData()
})
</script>

<style scoped>
.manage-academies {
	padding: 24px;
	max-width: 100%;
	overflow-x: auto;
	flex: 1;
	min-height: 0;
}

.header {
	margin-bottom: 24px;
}

.header h2 {
	color: #2d3748;
	font-size: 28px;
	margin: 0 0 8px 0;
	font-weight: 700;
}

.header p {
	color: #718096;
	margin: 0;
	font-size: 16px;
}

.table-tabs {
	display: flex;
	gap: 4px;
	margin-bottom: 24px;
	overflow-x: auto;
	padding-bottom: 8px;
}

.tab-button {
	background: white;
	border: 1px solid #e2e8f0;
	padding: 8px 16px;
	border-radius: 6px;
	font-size: 14px;
	font-weight: 500;
	cursor: pointer;
	transition: all 0.2s ease;
	white-space: nowrap;
	color: #4a5568;
}

.tab-button:hover {
	background: #f7fafc;
	border-color: #cbd5e0;
}

.tab-button.active {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
	border-color: transparent;
}

.table-container {
	background: white;
	border-radius: 8px;
	border: 1px solid #e2e8f0;
	overflow: hidden;
}

.table-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 16px 20px;
	border-bottom: 1px solid #e2e8f0;
	background: #f7fafc;
}

.table-header h3 {
	margin: 0;
	color: #2d3748;
	font-size: 18px;
	font-weight: 600;
}

.refresh-btn {
	background: #667eea;
	color: white;
	border: none;
	padding: 6px 12px;
	border-radius: 4px;
	font-size: 12px;
	cursor: pointer;
	transition: background-color 0.2s ease;
}

.refresh-btn:hover {
	background: #5a67d8;
}

.loading {
	padding: 40px;
	text-align: center;
	color: #718096;
	font-size: 16px;
}

.error {
	padding: 20px;
	text-align: center;
	color: #e53e3e;
	background: #fed7d7;
	margin: 16px;
	border-radius: 6px;
	border: 1px solid #feb2b2;
}

.no-data {
	padding: 40px;
	text-align: center;
	color: #718096;
	font-style: italic;
}

.data-table {
	overflow: auto;
	max-height: 60vh;
}

.data-grid {
	width: 100%;
	border-collapse: collapse;
}

.data-grid th {
	background: #f7fafc;
	color: #2d3748;
	font-weight: 600;
	padding: 12px 16px;
	text-align: left;
	border-bottom: 1px solid #e2e8f0;
	font-size: 14px;
	white-space: nowrap;
}

.data-grid td {
	padding: 12px 16px;
	border-bottom: 1px solid #e2e8f0;
	color: #4a5568;
	font-size: 14px;
	max-width: 200px;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}

.data-grid tr:hover {
	background: #f7fafc;
}

.data-grid tr:last-child td {
	border-bottom: none;
}

/* Action buttons styles */
.action-column {
	min-width: 150px;
	text-align: center;
}

.action-cell {
	text-align: center;
	white-space: nowrap;
}

.action-buttons {
	display: flex;
	gap: 8px;
	justify-content: center;
	align-items: center;
}

.accept-btn,
.reject-btn {
	padding: 4px 8px;
	border: none;
	border-radius: 4px;
	font-size: 12px;
	font-weight: 600;
	cursor: pointer;
	transition: all 0.2s ease;
	min-width: 60px;
}

.accept-btn {
	background: #10b981;
	color: white;
}

.accept-btn:hover:not(:disabled) {
	background: #059669;
}

.reject-btn {
	background: #ef4444;
	color: white;
}

.reject-btn:hover:not(:disabled) {
	background: #dc2626;
}

.accept-btn:disabled,
.reject-btn:disabled {
	opacity: 0.5;
	cursor: not-allowed;
}

/* Responsive Design */
@media (max-width: 768px) {
	.manage-academies {
		padding: 16px;
	}
	
	.table-tabs {
		flex-wrap: wrap;
	}
	
	.tab-button {
		font-size: 12px;
		padding: 6px 12px;
	}
	
	.table-header {
		flex-direction: column;
		gap: 12px;
		align-items: flex-start;
	}
	
	.data-grid th,
	.data-grid td {
		padding: 8px 12px;
		font-size: 12px;
	}
	
	.data-grid td {
		max-width: 150px;
	}
	
	.action-buttons {
		flex-direction: column;
		gap: 4px;
	}
	
	.accept-btn,
	.reject-btn {
		font-size: 10px;
		padding: 2px 6px;
		min-width: 50px;
	}
}

@media (max-width: 480px) {
	.header h2 {
		font-size: 24px;
	}
	
	.data-grid {
		font-size: 11px;
	}
	
	.data-grid th,
	.data-grid td {
		padding: 6px 8px;
	}
}
</style>
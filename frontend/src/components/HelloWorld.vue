<script setup>
import api from '@/services/api'

async function testConnection() {
    try {
        const response = await api.get('/categories')

        console.log(response.data)

    } catch (error) {
        console.error(error.response?.data || error.message)
    }
}

testConnection()
</script>
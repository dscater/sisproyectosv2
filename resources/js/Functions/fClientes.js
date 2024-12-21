import { ref } from 'vue';
import axios from 'axios';


export default function useClientes() {

    const errors = ref([]);
    const listClientes = ref([]);

    const getClientes = async () => {
        await axios.get("/clientes/get/listaClientes").then(response => {
            listClientes.value = response.data;
        });
    }

    const addCliente = async (data) => {
        await axios
            .post("/clientes", data)
            .then((response) => {
                getClientes();
            })
            .catch((error) => {
                if (error.response.status === 422) {
                    errors.value = error.response.data.errors;
                }
            });
    }

    const destroyCliente = async (id) => {
        await axios.delete("/clientes/" + id);
        getClientes();
    }

    return {
        listClientes,
        getClientes,
        errors,
        addCliente,
        destroyCliente
    }

}
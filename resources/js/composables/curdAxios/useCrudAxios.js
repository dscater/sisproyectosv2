import axios from "axios";
import { usePage } from "@inertiajs/vue3";

export const useCrudAxios = () => {
    let flash = null;
    const axiosGet = async (url, data) => {
        try {
            const response = await axios.get(url, {
                headers: { Accept: "application/json" },
                params: data,
            });
            flash = usePage().props.flash;
            return response.data;
        } catch (err) {
            flash = usePage().props.flash;
            Swal.fire({
                icon: "error",
                title: "Error",
                text: `${
                    flash.error
                        ? flash.error
                        : err.response?.data
                        ? err.response?.data?.message
                        : "Ocurrió un error inesperado y no se pudieron obtener los registros"
                }`,
                confirmButtonColor: "#3085d6",
                confirmButtonText: `Aceptar`,
            });
            throw err;
        } finally {
            flash = null;
        }
    };
    const axiosPostFormData = async (url, formdata) => {
        try {
            const response = await axios.post(url, formdata, {
                headers: {
                    Accept: "application/json",
                    "Content-Type": "multipart/form-data",
                },
            });
            flash = usePage().props.flash;
            Swal.fire({
                icon: "success",
                title: "Correcto",
                text: `${flash.bien ? flash.bien : "Proceso realizado"}`,
                confirmButtonColor: "#3085d6",
                confirmButtonText: `Aceptar`,
            });
            return response.data;
        } catch (err) {
            flash = usePage().props.flash;
            Swal.fire({
                icon: "error",
                title: "Error",
                text: `${
                    flash.error
                        ? flash.error
                        : err.response?.data
                        ? err.response?.data?.message
                        : "Hay errores en el formulario"
                }`,
                confirmButtonColor: "#3085d6",
                confirmButtonText: `Aceptar`,
            });
            throw err;
        } finally {
            flash = null;
        }
    };

    const axiosPost = async (url, data) => {
        try {
            const response = await axios.post(url, data, {
                headers: { Accept: "application/json" },
            });
            flash = usePage().props.flash;
            Swal.fire({
                icon: "success",
                title: "Correcto",
                text: `${flash.bien ? flash.bien : "Proceso realizado"}`,
                confirmButtonColor: "#3085d6",
                confirmButtonText: `Aceptar`,
            });
            return response.data;
        } catch (err) {
            flash = usePage().props.flash;
            Swal.fire({
                icon: "error",
                title: "Error",
                text: `${
                    flash.error
                        ? flash.error
                        : err.response?.data
                        ? err.response?.data?.message
                        : "Hay errores en el formulario"
                }`,
                confirmButtonColor: "#3085d6",
                confirmButtonText: `Aceptar`,
            });
            throw err;
        } finally {
            flash = null;
        }
    };

    const axiosDelete = async (url) => {
        try {
            const response = await axios.delete(url, {
                headers: { Accept: "application/json" },
            });
            flash = usePage().props.flash;
            Swal.fire({
                icon: "success",
                title: "Correcto",
                text: `${flash.bien ? flash.bien : "Proceso realizado"}`,
                confirmButtonColor: "#3085d6",
                confirmButtonText: `Aceptar`,
            });
            return response.data;
        } catch (err) {
            flash = usePage().props.flash;
            Swal.fire({
                icon: "error",
                title: "Error",
                text: `${
                    flash.error
                        ? flash.error
                        : err.response?.data
                        ? err.response?.data?.message
                        : "Ocurrió un error inesperado, no se pudo ejecutar la función"
                }`,
                confirmButtonColor: "#3085d6",
                confirmButtonText: `Aceptar`,
            });
            throw err;
        } finally {
            flash = null;
        }
    };

    return {
        axiosGet,
        axiosPost,
        axiosDelete,
        axiosPostFormData,
    };
};

export type Alumno = {
    id: number;
    dni: string;
    apellido: string;
    nombre: string;
    fecha_nacimiento: string;
    genero: string | null;
    direccion: string | null;
    telefono: string | null;
    nombre_tutor: string | null;
    telefono_tutor: string | null;
    email_tutor: string | null;
    fecha_ingreso: string;
    activo: boolean;
    created_at: string;
    updated_at: string;
};

export type Profesor = {
    id: number;
    dni: string;
    apellido: string;
    nombre: string;
    fecha_nacimiento: string;
    direccion: string | null;
    telefono: string | null;
    fecha_ingreso: string;
    activo: boolean;
    created_at: string;
    updated_at: string;
    user: {
        id: number;
        name: string;
        email: string;
    };
};

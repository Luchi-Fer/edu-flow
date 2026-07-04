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

export type Usuario = {
    id: number;
    name: string;
    email: string;
    roles: { id: number; name: string }[];
    created_at: string;
    updated_at: string;
};

export type Materia = {
    id: number;
    nombre: string;
    descripcion: string | null;
    created_at: string;
    updated_at: string;
};

export type CicloLectivo = {
    id: number;
    anio: number;
    fecha_inicio: string;
    fecha_fin: string;
    activo: boolean;
    created_at: string;
    updated_at: string;
};

export type Curso = {
    id: number;
    ciclo_lectivo_id: number;
    nivel: 'primaria' | 'secundaria';
    anio: number;
    division: string;
    turno: string | null;
    label: string;
    created_at: string;
    updated_at: string;
    ciclo_lectivo: Pick<CicloLectivo, 'id' | 'anio'>;
};

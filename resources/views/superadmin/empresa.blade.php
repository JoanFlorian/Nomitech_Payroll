@extends('layouts.superadmin')

import { Building2, Truck, Rocket, Construction, HeartPulse, ShoppingBag } from "lucide-react";

export default function DirectorioEmpresasView() {
  return (
    <div className="space-y-6">
      {/* Tabs */}
      <div className="flex gap-6 text-sm border-b">
        <Tab label="Todas" active />
        <Tab label="Activas" />
        <Tab label="Vencidas" />
        <Tab label="Periodo de Prueba" />
      </div>

      {/* Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <EmpresaCard
          icon={<Building2 />}
          nombre="TechSolutions S.A."
          nit="900.123.456-1"
          estado="ACTIVA"
          licencia="1 Año"
          empleados={124}
        />

        <EmpresaCard
          icon={<Truck />}
          nombre="Logística Global"
          nit="830.555.222-0"
          estado="ACTIVA"
          licencia="6 Meses"
          empleados={85}
        />

        <EmpresaCard
          icon={<Rocket />}
          nombre="Innovación Digital"
          nit="901.888.555-5"
          estado="PRUEBA"
          licencia="Demo"
          empleados={42}
        />

        <EmpresaCard
          icon={<Construction />}
          nombre="Constructora Alfa"
          nit="800.777.444-9"
          estado="VENCIDA"
          licencia="Mensual"
          empleados={156}
        />

        <EmpresaCard
          icon={<HeartPulse />}
          nombre="Servicios Médicos Plus"
          nit="900.444.111-2"
          estado="ACTIVA"
          licencia="2 Años"
          empleados={310}
        />

        <EmpresaCard
          icon={<ShoppingBag />}
          nombre="Retail Nexus"
          nit="860.999.000-4"
          estado="ACTIVA"
          licencia="1 Año"
          empleados={67}
        />
      </div>
    </div>
  );
}

function Tab({ label, active }) {
  return (
    <button
      className={`pb-3 transition ${
        active
          ? "border-b-2 border-blue-600 text-blue-600 font-medium"
          : "text-gray-500 hover:text-gray-700"
      }`}
    >
      {label}
    </button>
  );
}

function EmpresaCard({ icon, nombre, nit, estado, licencia, empleados }) {
  const estadoStyles = {
    ACTIVA: "bg-green-100 text-green-700",
    PRUEBA: "bg-blue-100 text-blue-700",
    VENCIDA: "bg-red-100 text-red-700",
  };

  return (
    <div className="bg-white rounded-2xl shadow-sm border p-5 space-y-4">
      <div className="flex items-start justify-between">
        <div className="flex items-center gap-3">
          <div className="h-10 w-10 rounded-xl bg-gray-100 flex items-center justify-center text-blue-600">
            {icon}
          </div>
          <div>
            <p className="font-semibold">{nombre}</p>
            <p className="text-xs text-gray-500">NIT: {nit}</p>
          </div>
        </div>
        <span
          className={`text-xs font-medium px-2 py-1 rounded-full ${
            estadoStyles[estado]
          }`}
        >
          {estado}
        </span>
      </div>

      <div className="flex justify-between text-sm text-gray-600">
        <div>
          <p className="text-xs">Licencia</p>
          <p className="font-medium text-gray-800">{licencia}</p>
        </div>
        <div>
          <p className="text-xs">Empleados</p>
          <p className="font-medium text-gray-800">{empleados}</p>
        </div>
      </div>

      <button className="w-full text-sm py-2 rounded-lg bg-gray-100 hover:bg-gray-200 transition">
        Ver Detalles
      </button>
    </div>
  );
}

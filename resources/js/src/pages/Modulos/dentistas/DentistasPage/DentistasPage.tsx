import React, { useEffect, useMemo, useState } from "react";
import api from "../../../../components/api/api";
import styled from "styled-components";
import * as XLSX from "xlsx";

type DiaSemana = "segunda" | "terca" | "quarta" | "quinta" | "sexta" | "sabado" | "domingo";

interface HorarioAtendimento {
  dia_semana: DiaSemana;
  ativo: boolean;
  hora_inicio: string;
  hora_fim: string;
}

interface Dentista {
  id: number;
  name: string;
  email: string | null;
  telefone: string | null;
  celular: string | null;
  cpf: string | null;
  cro: string;
  cro_uf: string;
  especialidade: string;
  data_nascimento: string | null;
  data_cadastro: string | null;
  intervalo_consulta: number;
  horarios_atendimento: HorarioAtendimento[] | null;
  chave_pix: string | null;
  cep: string | null;
  rua: string | null;
  numero: string | null;
  complemento: string | null;
  bairro: string | null;
  cidade: string | null;
  estado: string | null;
  status: boolean;
  created_at: string;
  updated_at: string;
}

type DentistaFormData = Omit<Dentista, "id" | "created_at" | "updated_at">;

const dias: { key: DiaSemana; label: string }[] = [
  { key: "segunda", label: "Segunda" },
  { key: "terca", label: "Terca" },
  { key: "quarta", label: "Quarta" },
  { key: "quinta", label: "Quinta" },
  { key: "sexta", label: "Sexta" },
  { key: "sabado", label: "Sabado" },
  { key: "domingo", label: "Domingo" },
];

const defaultHorarios = (): HorarioAtendimento[] =>
  dias.map((d) => ({
    dia_semana: d.key,
    ativo: ["segunda", "terca", "quarta", "quinta", "sexta"].includes(d.key),
    hora_inicio: "08:00",
    hora_fim: "18:00",
  }));

const toDateBr = (value?: string | null) => {
  if (!value) return "-";
  const m = String(value).match(/^(\d{4})-(\d{2})-(\d{2})/);
  if (!m) return value;
  return `${m[3]}/${m[2]}/${m[1]}`;
};

const normalizeHorarios = (value: any): HorarioAtendimento[] => {
  if (!Array.isArray(value)) return defaultHorarios();

  const byKey = new Map<string, HorarioAtendimento>();
  value.forEach((item) => {
    if (!item?.dia_semana) return;
    byKey.set(String(item.dia_semana), {
      dia_semana: item.dia_semana,
      ativo: Boolean(item.ativo),
      hora_inicio: String(item.hora_inicio || "08:00"),
      hora_fim: String(item.hora_fim || "18:00"),
    });
  });

  return dias.map((d) => byKey.get(d.key) || {
    dia_semana: d.key,
    ativo: false,
    hora_inicio: "08:00",
    hora_fim: "18:00",
  });
};

const horariosResumo = (dentista: Dentista) => {
  const horarios = normalizeHorarios(dentista.horarios_atendimento);
  const ativos = horarios.filter((h) => h.ativo);
  if (ativos.length === 0) return "Sem horario";
  const primeiro = ativos[0];
  return `${ativos.length} dias: ${primeiro.hora_inicio}-${primeiro.hora_fim} | ${dentista.intervalo_consulta} min`;
};

const pageStyle = {
  padding: "20px",
  backgroundColor: "#fff",
  margin: "20px",
  borderRadius: "8px",
  boxShadow: "0 4px 6px rgba(0,0,0,0.1)",
  minHeight: "calc(100vh - 40px)",
};

const Button = styled.button<{ color?: string; hover?: string }>`
  padding: 10px 14px;
  border: none;
  border-radius: 6px;
  color: #fff;
  cursor: pointer;
  font-weight: 600;
  background: ${(p) => p.color || "#007bff"};
  transition: 0.2s ease;

  &:hover {
    background: ${(p) => p.hover || "#0056b3"};
  }
`;

const Overlay = styled.div`
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  z-index: 999;
`;

const Modal = styled.div`
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: min(980px, 92vw);
  max-height: 88vh;
  overflow: auto;
  background: #fff;
  border-radius: 10px;
  padding: 20px;
  z-index: 1000;
`;

const Table = styled.table`
  width: 100%;
  border-collapse: collapse;

  th,
  td {
    border: 1px solid #e6e6e6;
    padding: 10px;
    text-align: left;
    vertical-align: middle;
  }

  th {
    background: #f8f9fa;
    position: sticky;
    top: 0;
  }
`;

const DentistaForm: React.FC<{
  title: string;
  data: DentistaFormData;
  especialidades: string[];
  estados: string[];
  onChange: (next: DentistaFormData) => void;
  onCancel: () => void;
  onSubmit: () => void;
}> = ({ title, data, especialidades, estados, onChange, onCancel, onSubmit }) => {
  const set = (field: keyof DentistaFormData, value: any) => onChange({ ...data, [field]: value });

  const setHorario = (dia: DiaSemana, patch: Partial<HorarioAtendimento>) => {
    const next = normalizeHorarios(data.horarios_atendimento).map((h) =>
      h.dia_semana === dia ? { ...h, ...patch } : h
    );
    set("horarios_atendimento", next);
  };

  const horarios = normalizeHorarios(data.horarios_atendimento);

  return (
    <div>
      <h2>{title}</h2>
      <div style={{ display: "grid", gap: 12 }}>
        <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 }}>
          <div>
            <label>Nome *</label>
            <input value={data.name} onChange={(e) => set("name", e.target.value)} style={{ width: "100%" }} />
          </div>
          <div>
            <label>Especialidade *</label>
            <select value={data.especialidade} onChange={(e) => set("especialidade", e.target.value)} style={{ width: "100%" }}>
              <option value="">Selecione</option>
              {especialidades.map((esp) => (<option key={esp} value={esp}>{esp}</option>))}
            </select>
          </div>
        </div>

        <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr 1fr", gap: 12 }}>
          <div>
            <label>CRO *</label>
            <input value={data.cro} onChange={(e) => set("cro", e.target.value)} style={{ width: "100%" }} />
          </div>
          <div>
            <label>UF CRO *</label>
            <select value={data.cro_uf} onChange={(e) => set("cro_uf", e.target.value)} style={{ width: "100%" }}>
              <option value="">UF</option>
              {estados.map((uf) => (<option key={uf} value={uf}>{uf}</option>))}
            </select>
          </div>
          <div>
            <label>Intervalo (min)</label>
            <input type="number" min={5} max={240} step={5} value={data.intervalo_consulta || 30} onChange={(e) => set("intervalo_consulta", Number(e.target.value))} style={{ width: "100%" }} />
          </div>
        </div>

        <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr 1fr", gap: 12 }}>
          <div>
            <label>Email</label>
            <input type="email" value={data.email || ""} onChange={(e) => set("email", e.target.value)} style={{ width: "100%" }} />
          </div>
          <div>
            <label>Telefone</label>
            <input value={data.telefone || ""} onChange={(e) => set("telefone", e.target.value)} style={{ width: "100%" }} />
          </div>
          <div>
            <label>Celular</label>
            <input value={data.celular || ""} onChange={(e) => set("celular", e.target.value)} style={{ width: "100%" }} />
          </div>
        </div>

        <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr 1fr", gap: 12 }}>
          <div>
            <label>CPF</label>
            <input value={data.cpf || ""} onChange={(e) => set("cpf", e.target.value)} style={{ width: "100%" }} />
          </div>
          <div>
            <label>Data nascimento</label>
            <input type="date" value={data.data_nascimento || ""} onChange={(e) => set("data_nascimento", e.target.value)} style={{ width: "100%" }} />
          </div>
          <div>
            <label>Data cadastro</label>
            <input type="date" value={data.data_cadastro || ""} onChange={(e) => set("data_cadastro", e.target.value)} style={{ width: "100%" }} />
          </div>
        </div>

        <div style={{ border: "1px solid #e5e5e5", borderRadius: 8, padding: 12 }}>
          <h3 style={{ marginTop: 0 }}>Horario de atendimento (padrao agendamento)</h3>
          {horarios.map((h) => (
            <div key={h.dia_semana} style={{ display: "grid", gridTemplateColumns: "160px 120px 140px 140px", gap: 10, alignItems: "center", marginBottom: 8 }}>
              <strong>{dias.find((d) => d.key === h.dia_semana)?.label}</strong>
              <label style={{ display: "flex", gap: 8 }}>
                <input type="checkbox" checked={h.ativo} onChange={(e) => setHorario(h.dia_semana, { ativo: e.target.checked })} />
                Atende
              </label>
              <input type="time" step={1800} value={h.hora_inicio} disabled={!h.ativo} onChange={(e) => setHorario(h.dia_semana, { hora_inicio: e.target.value })} />
              <input type="time" step={1800} value={h.hora_fim} disabled={!h.ativo} onChange={(e) => setHorario(h.dia_semana, { hora_fim: e.target.value })} />
            </div>
          ))}
        </div>

        <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr 1fr 1fr", gap: 12 }}>
          <div>
            <label>CEP</label>
            <input value={data.cep || ""} onChange={(e) => set("cep", e.target.value)} style={{ width: "100%" }} />
          </div>
          <div>
            <label>Cidade</label>
            <input value={data.cidade || ""} onChange={(e) => set("cidade", e.target.value)} style={{ width: "100%" }} />
          </div>
          <div>
            <label>Estado</label>
            <select value={data.estado || ""} onChange={(e) => set("estado", e.target.value)} style={{ width: "100%" }}>
              <option value="">UF</option>
              {estados.map((uf) => (<option key={uf} value={uf}>{uf}</option>))}
            </select>
          </div>
          <div>
            <label>Status</label>
            <select value={String(data.status)} onChange={(e) => set("status", e.target.value === "true")} style={{ width: "100%" }}>
              <option value="true">Ativo</option>
              <option value="false">Inativo</option>
            </select>
          </div>
        </div>

        <div style={{ display: "grid", gridTemplateColumns: "1fr 140px 1fr", gap: 12 }}>
          <div>
            <label>Rua</label>
            <input value={data.rua || ""} onChange={(e) => set("rua", e.target.value)} style={{ width: "100%" }} />
          </div>
          <div>
            <label>Numero</label>
            <input value={data.numero || ""} onChange={(e) => set("numero", e.target.value)} style={{ width: "100%" }} />
          </div>
          <div>
            <label>Bairro</label>
            <input value={data.bairro || ""} onChange={(e) => set("bairro", e.target.value)} style={{ width: "100%" }} />
          </div>
        </div>

        <div style={{ display: "flex", justifyContent: "flex-end", gap: 10, marginTop: 10 }}>
          <Button color="#6c757d" hover="#5a6268" onClick={onCancel}>Cancelar</Button>
          <Button color="#007bff" hover="#0056b3" onClick={onSubmit}>Salvar</Button>
        </div>
      </div>
    </div>
  );
};

const emptyForm = (): DentistaFormData => ({
  name: "",
  email: "",
  telefone: "",
  celular: "",
  cpf: "",
  cro: "",
  cro_uf: "",
  especialidade: "",
  data_nascimento: "",
  data_cadastro: new Date().toISOString().split("T")[0],
  intervalo_consulta: 30,
  horarios_atendimento: defaultHorarios(),
  chave_pix: "",
  cep: "",
  rua: "",
  numero: "",
  complemento: "",
  bairro: "",
  cidade: "",
  estado: "",
  status: true,
});

const DentistasPage: React.FC = () => {
  const [dentistas, setDentistas] = useState<Dentista[]>([]);
  const [especialidades, setEspecialidades] = useState<string[]>([]);
  const [estados, setEstados] = useState<string[]>([]);
  const [currentPage, setCurrentPage] = useState(1);
  const [showAdd, setShowAdd] = useState(false);
  const [showEdit, setShowEdit] = useState(false);
  const [showView, setShowView] = useState(false);
  const [selected, setSelected] = useState<Dentista | null>(null);
  const [form, setForm] = useState<DentistaFormData>(emptyForm());
  const [message, setMessage] = useState("");
  const itemsPerPage = 10;

  const totalPages = Math.max(1, Math.ceil(dentistas.length / itemsPerPage));
  const pageItems = useMemo(
    () => dentistas.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage),
    [dentistas, currentPage]
  );

  const loadDentistas = async () => {
    const response = await api.get("/pessoas/dentistas", { headers: { Accept: "application/json" } });
    const list = Array.isArray(response.data) ? response.data : response.data?.data || [];
    const normalized = list.map((d: Dentista) => ({ ...d, horarios_atendimento: normalizeHorarios(d.horarios_atendimento) }));
    setDentistas(normalized);
  };

  useEffect(() => {
    loadDentistas().catch(() => setMessage("Erro ao carregar dentistas"));

    api.get("/pessoas/dentistas/reference/especialidades", { headers: { Accept: "application/json" } })
      .then((r) => setEspecialidades(r.data?.especialidades || []))
      .catch(() => setEspecialidades([]));

    api.get("/pessoas/dentistas/reference/estados", { headers: { Accept: "application/json" } })
      .then((r) => setEstados(r.data?.estados || []))
      .catch(() => setEstados([]));
  }, []);

  const saveAdd = async () => {
    if (!form.name || !form.cro || !form.cro_uf || !form.especialidade) {
      setMessage("Preencha Nome, CRO, UF do CRO e Especialidade.");
      return;
    }

    try {
      await api.post("/pessoas/dentistas", form, { headers: { Accept: "application/json" } });
      await loadDentistas();
      setShowAdd(false);
      setForm(emptyForm());
      setMessage("Dentista cadastrado com sucesso.");
      setCurrentPage(totalPages);
    } catch (error: any) {
      setMessage(error?.response?.data?.message || "Erro ao cadastrar dentista");
    }
  };

  const saveEdit = async () => {
    if (!selected) return;
    if (!form.name || !form.cro || !form.cro_uf || !form.especialidade) {
      setMessage("Preencha Nome, CRO, UF do CRO e Especialidade.");
      return;
    }

    try {
      await api.put(`/pessoas/dentistas/${selected.id}`, form, { headers: { Accept: "application/json" } });
      await loadDentistas();
      setShowEdit(false);
      setSelected(null);
      setMessage("Dentista atualizado com sucesso.");
    } catch (error: any) {
      setMessage(error?.response?.data?.message || "Erro ao atualizar dentista");
    }
  };

  const deleteDentista = async (item: Dentista) => {
    if (!window.confirm(`Deseja excluir o dentista ${item.name}?`)) return;

    try {
      await api.delete(`/pessoas/dentistas/${item.id}`, { headers: { Accept: "application/json" } });
      await loadDentistas();
      setMessage("Dentista excluido com sucesso.");
    } catch (error: any) {
      setMessage(error?.response?.data?.message || "Erro ao excluir dentista");
    }
  };

  const exportExcel = () => {
    if (dentistas.length === 0) return;
    const data = dentistas.map((d) => ({
      ID: d.id,
      Nome: d.name,
      Especialidade: d.especialidade,
      CRO: `${d.cro}/${d.cro_uf}`,
      Telefone: d.telefone || "",
      Email: d.email || "",
      Horario: horariosResumo(d),
      Status: d.status ? "Ativo" : "Inativo",
    }));
    const ws = XLSX.utils.json_to_sheet(data);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Dentistas");
    XLSX.writeFile(wb, "Dentistas.xlsx");
  };

  return (
    <div style={pageStyle}>
      <div style={{ display: "flex", justifyContent: "space-between", marginBottom: 16 }}>
        <h2>Lista de Dentistas</h2>
        <div style={{ display: "flex", gap: 10 }}>
          <Button color="#007bff" hover="#0056b3" onClick={() => { setForm(emptyForm()); setShowAdd(true); }}>
            + Adicionar Dentista
          </Button>
          <Button color="#28a745" hover="#218838" onClick={exportExcel}>Exportar Excel</Button>
        </div>
      </div>

      {message && <div style={{ background: "#e8f5e9", border: "1px solid #81c784", padding: 10, borderRadius: 6, marginBottom: 12 }}>{message}</div>}

      <div style={{ border: "1px solid #e6e6e6", borderRadius: 8, overflow: "auto", maxHeight: "calc(100vh - 290px)" }}>
        <Table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nome</th>
              <th>Especialidade</th>
              <th>CRO</th>
              <th>Telefone</th>
              <th>Horario Atendimento</th>
              <th>Status</th>
              <th>Acoes</th>
            </tr>
          </thead>
          <tbody>
            {pageItems.map((d) => (
              <tr key={d.id}>
                <td>{d.id}</td>
                <td>{d.name}</td>
                <td>{d.especialidade}</td>
                <td>{d.cro}/{d.cro_uf}</td>
                <td>{d.telefone || d.celular || "-"}</td>
                <td>{horariosResumo(d)}</td>
                <td>{d.status ? "Ativo" : "Inativo"}</td>
                <td>
                  <div style={{ display: "flex", gap: 6 }}>
                    <Button color="#17a2b8" hover="#138496" onClick={() => { setSelected(d); setShowView(true); }}>Ver</Button>
                    <Button color="#ffc107" hover="#e0a800" onClick={() => { setSelected(d); setForm({ ...d, horarios_atendimento: normalizeHorarios(d.horarios_atendimento) }); setShowEdit(true); }}>Editar</Button>
                    <Button color="#dc3545" hover="#c82333" onClick={() => deleteDentista(d)}>Excluir</Button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </Table>
      </div>

      <div style={{ display: "flex", justifyContent: "center", alignItems: "center", marginTop: 12, gap: 12 }}>
        <Button color="#6c757d" hover="#5a6268" onClick={() => setCurrentPage((p) => Math.max(1, p - 1))}>Anterior</Button>
        <strong>Pagina {currentPage} de {totalPages}</strong>
        <Button color="#6c757d" hover="#5a6268" onClick={() => setCurrentPage((p) => Math.min(totalPages, p + 1))}>Proxima</Button>
      </div>

      {showAdd && (
        <>
          <Overlay onClick={() => setShowAdd(false)} />
          <Modal>
            <DentistaForm
              title="Adicionar Dentista"
              data={form}
              especialidades={especialidades}
              estados={estados}
              onChange={setForm}
              onCancel={() => setShowAdd(false)}
              onSubmit={saveAdd}
            />
          </Modal>
        </>
      )}

      {showEdit && selected && (
        <>
          <Overlay onClick={() => setShowEdit(false)} />
          <Modal>
            <DentistaForm
              title={`Editar Dentista #${selected.id}`}
              data={form}
              especialidades={especialidades}
              estados={estados}
              onChange={setForm}
              onCancel={() => setShowEdit(false)}
              onSubmit={saveEdit}
            />
          </Modal>
        </>
      )}

      {showView && selected && (
        <>
          <Overlay onClick={() => setShowView(false)} />
          <Modal>
            <h2>Dados completos do Dentista</h2>
            <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 }}>
              <div><strong>Nome:</strong> {selected.name}</div>
              <div><strong>Especialidade:</strong> {selected.especialidade}</div>
              <div><strong>CRO:</strong> {selected.cro}/{selected.cro_uf}</div>
              <div><strong>CPF:</strong> {selected.cpf || "-"}</div>
              <div><strong>Email:</strong> {selected.email || "-"}</div>
              <div><strong>Telefone:</strong> {selected.telefone || "-"}</div>
              <div><strong>Celular:</strong> {selected.celular || "-"}</div>
              <div><strong>Data nascimento:</strong> {toDateBr(selected.data_nascimento)}</div>
              <div><strong>Data cadastro:</strong> {toDateBr(selected.data_cadastro)}</div>
              <div><strong>Intervalo:</strong> {selected.intervalo_consulta} min</div>
              <div><strong>Status:</strong> {selected.status ? "Ativo" : "Inativo"}</div>
              <div style={{ gridColumn: "1 / span 2" }}>
                <strong>Endereco:</strong> {selected.rua || ""} {selected.numero || ""} {selected.complemento || ""} - {selected.bairro || ""}, {selected.cidade || ""}/{selected.estado || ""}
              </div>
              <div style={{ gridColumn: "1 / span 2" }}>
                <strong>Horarios:</strong>
                <ul style={{ marginTop: 6 }}>
                  {normalizeHorarios(selected.horarios_atendimento).map((h) => (
                    <li key={h.dia_semana}>{dias.find((d) => d.key === h.dia_semana)?.label}: {h.ativo ? `${h.hora_inicio} - ${h.hora_fim}` : "Nao atende"}</li>
                  ))}
                </ul>
              </div>
            </div>
            <div style={{ marginTop: 10, display: "flex", justifyContent: "flex-end" }}>
              <Button onClick={() => setShowView(false)}>Fechar</Button>
            </div>
          </Modal>
        </>
      )}
    </div>
  );
};

export default DentistasPage;

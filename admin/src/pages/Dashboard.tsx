const Dashboard = () => {
    return (
        <div>
            <h1 style={{fontSize: '24px', fontWeight: 600, marginBottom: '20px'}}>Dashboard</h1>
            <div
                style={{
                    display: 'grid',
                    gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))',
                    gap: '20px'
                }}>
                <div style={{padding: '20px', background: 'var(--fondoSecundario)', borderRadius: '8px', border: '1px solid var(--bordeColor)'}}>
                    <h3 style={{color: 'var(--textoSecundario)', fontSize: '13px', marginBottom: '8px'}}>Contenidos</h3>
                    <p style={{fontSize: '28px', fontWeight: 700}}>124</p>
                </div>
                <div style={{padding: '20px', background: 'var(--fondoSecundario)', borderRadius: '8px', border: '1px solid var(--bordeColor)'}}>
                    <h3 style={{color: 'var(--textoSecundario)', fontSize: '13px', marginBottom: '8px'}}>Usuarios</h3>
                    <p style={{fontSize: '28px', fontWeight: 700}}>1,203</p>
                </div>
                <div style={{padding: '20px', background: 'var(--fondoSecundario)', borderRadius: '8px', border: '1px solid var(--bordeColor)'}}>
                    <h3 style={{color: 'var(--textoSecundario)', fontSize: '13px', marginBottom: '8px'}}>Comentarios</h3>
                    <p style={{fontSize: '28px', fontWeight: 700}}>85</p>
                </div>
            </div>
        </div>
    );
};

export default Dashboard;

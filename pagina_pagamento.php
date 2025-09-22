<?php
session_start();
$total = isset($_GET['amount']) ? floatval($_GET['amount']) : 49.90;
?>

<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Página de Pagamento </title>
  <style>
    :root{--bg:#0f1720;--card:#111827;--muted:#94a3b8;--accent:#16a34a;--glass:rgba(255,255,255,0.03)}
    *{box-sizing:border-box;font-family:Inter, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial}
    body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(180deg,#071029 0%, #071a2b 100%);color:#e6eef6}
    .container{width:380px;padding:20px;border-radius:14px;background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));box-shadow:0 8px 30px rgba(2,6,23,0.7)}
    header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
    header h1{font-size:18px;margin:0}
    header p{margin:0;color:var(--muted);font-size:13px}

    .card{background:var(--card);padding:16px;border-radius:12px}

    .tabs{display:flex;gap:6px;margin-bottom:12px}
    .tab{flex:1;text-align:center;padding:8px 6px;border-radius:8px;background:var(--glass);cursor:pointer;font-size:14px}
    .tab.active{background:linear-gradient(90deg,#052b15,#0b5132);box-shadow:0 4px 18px rgba(3,79,46,0.25)}

    .panel{display:none}
    .panel.active{display:block}

    .pix-code{background:#061b0f;border:1px dashed rgba(255,255,255,0.04);padding:12px;border-radius:8px;text-align:center;font-family:monospace;letter-spacing:0.6px}
    .btn{display:inline-block;padding:10px 14px;border-radius:10px;border:0;cursor:pointer;font-weight:600}
    .btn-ghost{background:transparent;color:var(--accent);border:1px solid rgba(22,163,74,0.12)}
    .btn-primary{background:var(--accent);color:#08220f;text-decoration:none}

    .boleto-number{font-family:monospace;background:#071422;padding:10px;border-radius:8px;text-align:center}

    .form-row{display:flex;flex-direction:column;margin-bottom:10px}
    label{font-size:12px;color:var(--muted);margin-bottom:6px}
    input[type="text"], input[type="tel"], input[type="month"], input[type="password"]{background:#08121a;border:1px solid rgba(255,255,255,0.03);padding:10px;border-radius:8px;color:#e6eef6}
    .small{font-size:13px;color:var(--muted)}

    .actions{display:flex;gap:8px;justify-content:flex-end;margin-top:12px}

    .note{font-size:12px;color:var(--muted);margin-top:10px}

    @media (max-width:420px){.container{width:92%;padding:16px}}
  </style>
</head>
<body>
  <div class="container" role="main">
    <header>
      <div>
        <h1>Pagar compra</h1>
        <p>Escolha a forma de pagamento</p>
      </div>
      <div class="small">Total: <strong>R$ <?php echo number_format($total,2,',','.'); ?></strong></div>
    </header>

    <div class="card">
      <div class="tabs" role="tablist">
        <button class="tab active" data-target="pix" role="tab">PIX</button>
        <button class="tab" data-target="boleto" role="tab">Boleto</button>
        <button class="tab" data-target="card" role="tab">Cartão</button>
      </div>

      <div id="pix" class="panel active" aria-hidden="false">
        <div class="pix-code" id="pixCode">00020126360014BR.GOV.BCB.PIX0136pix-NEXUS-1234567895204<?php echo number_format($total,2,'',''); ?>530398654<?php echo number_format($total,2,'',''); ?></div>
        <div style="display:flex;justify-content:center;gap:8px;margin-top:12px">
          <button class="btn btn-ghost" id="copyPix">Copiar código</button>
          <button class="btn btn-primary" id="pixOk">OK</button>
        </div>
       
      </div>

      <div id="boleto" class="panel" aria-hidden="true">
        <p class="small">Clique para gerar um boleto de teste. O número pode ser copiado.</p>
        <div class="boleto-number" id="boletoNumber">— — —</div>
        <div style="display:flex;justify-content:center;gap:8px;margin-top:12px">
          <button class="btn btn-ghost" id="genBoleto">Gerar boleto</button>
          <button class="btn btn-primary" id="boletoOk">OK</button>
        </div>
       
      </div>

      <div id="card" class="panel" aria-hidden="true">
        <form id="cardForm" method="post" onsubmit="return false;">
          <div class="form-row">
            <label for="cardNumber">Número do cartão</label>
            <input id="cardNumber" type="tel" inputmode="numeric" maxlength="19" placeholder="0000 0000 0000 0000">
          </div>
          <div class="form-row" style="display:flex;gap:8px">
            <div style="flex:2">
              <label for="exp">Validade</label>
              <input id="exp" type="text" placeholder="MM/AA" maxlength="5">
            </div>
            <div style="flex:1">
              <label for="cvc">CVC</label>
              <input id="cvc" type="password" inputmode="numeric" maxlength="4" placeholder="123">
            </div>
          </div>
          <div class="form-row">
            <label for="holder">Nome no cartão</label>
            <input id="holder" type="text" placeholder="NOME DO TITULAR">
          </div>

          <div class="actions">
            <button class="btn btn-ghost" id="cardCancel" type="button">Cancelar</button>
            <button class="btn btn-primary" id="cardPay" type="submit">Pagar</button>
          </div>
          <p class="note" id="cardMsg" style="display:none"></p>
        </form>
      </div>
    </div>

    <!-- Botão para voltar ao início (aparece só após confirmar pagamento) -->
    <div id="backHome" style="display:none;text-align:center;margin-top:16px">
      <a href="index.php" class="btn btn-primary">Voltar para o início</a>
    </div>

    
  </div>

  <script>
    document.querySelectorAll('.tab').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
        document.querySelectorAll('.panel').forEach(p=>{p.classList.remove('active');p.setAttribute('aria-hidden','true')});
        btn.classList.add('active');
        const target = document.getElementById(btn.dataset.target);
        target.classList.add('active');
        target.setAttribute('aria-hidden','false');
      })
    });

    const pixCodeEl = document.getElementById('pixCode');
    document.getElementById('copyPix').addEventListener('click', ()=>{
      navigator.clipboard?.writeText(pixCodeEl.textContent).then(()=>{alert('Código PIX copiado!');});
    });
    document.getElementById('pixOk').addEventListener('click', ()=>{
      alert('Pagamento via PIX (simulado) confirmado.');
      document.getElementById('backHome').style.display = 'block';
    });

    function genBoletoNumber(){const parts=[];for(let i=0;i<5;i++) parts.push(Math.floor(Math.random()*90000)+10000);return parts.join(' ');}
    document.getElementById('genBoleto').addEventListener('click', ()=>{
      document.getElementById('boletoNumber').textContent = genBoletoNumber();
    });
    document.getElementById('boletoOk').addEventListener('click', ()=>{
      alert('Boleto (simulado) gerado e confirmado.');
      document.getElementById('backHome').style.display = 'block';
    });

    const cardNumber = document.getElementById('cardNumber');
    const exp = document.getElementById('exp');
    const cvc = document.getElementById('cvc');
    const holder = document.getElementById('holder');
    const cardMsg = document.getElementById('cardMsg');

    cardNumber.addEventListener('input', e=>{let v=e.target.value.replace(/\D/g,'').slice(0,16);v=v.replace(/(\d{4})(?=\d)/g,'$1 ');e.target.value=v;});
    exp.addEventListener('input', e=>{let v=e.target.value.replace(/\D/g,'').slice(0,4);if(v.length>2)v=v.slice(0,2)+'/'+v.slice(2);e.target.value=v;});
    cvc.addEventListener('input', e=>{e.target.value=e.target.value.replace(/\D/g,'').slice(0,4);});

    document.getElementById('cardPay').addEventListener('click', ()=>{
      if(cardNumber.value.replace(/\s/g,'').length < 13){showCardMsg('Número do cartão inválido');return}
      if(!/^(0[1-9]|1[0-2])\/\d{2}$/.test(exp.value)){showCardMsg('Validade inválida (MM/AA)');return}
      if(cvc.value.length < 3){showCardMsg('CVC inválido');return}
      if(holder.value.trim().length < 3){showCardMsg('Nome do titular inválido');return}
      showCardMsg('Pagamento processado (simulado). Obrigado!', true);
      setTimeout(()=>{
        alert('Pagamento com cartão (simulado) concluído.');
        document.getElementById('backHome').style.display = 'block';
      },300);
    });
    document.getElementById('cardCancel').addEventListener('click', ()=>{
      cardNumber.value='';exp.value='';cvc.value='';holder.value='';cardMsg.style.display='none';
    });
    function showCardMsg(text, ok=false){cardMsg.style.display='block';cardMsg.textContent=text;cardMsg.style.color=ok?'#8ef08a':'#ffb4b4';}
  </script>
</body>
</html>

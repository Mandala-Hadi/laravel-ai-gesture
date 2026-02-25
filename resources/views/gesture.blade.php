<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Mode Gesture AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-slate-900 via-gray-900 to-black text-white min-h-screen relative p-6">

<!-- Tombol Kembali -->
<button onclick="goBack()" 
    class="absolute top-6 left-6 bg-white/10 hover:bg-white/20 
    backdrop-blur-md px-4 py-2 rounded-xl 
    transition duration-300 shadow-lg">
    Kembali
</button>

<div class="max-w-5xl mx-auto mt-16">

    <!-- Header -->
    <div class="text-center mb-10">
        <div class="flex justify-center mb-4">
            <div class="bg-blue-600/20 p-5 rounded-full animate-pulse text-3xl">
                🤖
            </div>
        </div>

        <h1 class="text-4xl font-bold mb-3 tracking-wide">
            Mode Gesture AI
        </h1>

        <p class="text-gray-400 text-lg">
            Kelola gesture dan kontrol sistem AI secara real-time.
        </p>

        <div class="mt-6 space-x-4">
            <button onclick="startAI()"
                class="bg-green-600 hover:bg-green-700 px-6 py-3 rounded-xl shadow-lg transition">
                Mulai AI
            </button>

            <button onclick="stopAI()"
                class="bg-red-600 hover:bg-red-700 px-6 py-3 rounded-xl shadow-lg transition">
                Stop AI
            </button>
        </div>
    </div>

    <!-- Form Tambah Gesture -->
    <div class="bg-white/5 backdrop-blur-xl p-6 rounded-2xl shadow-xl border border-white/10 mb-8">
        <h2 class="text-xl font-semibold mb-4">Tambah Gesture</h2>

        <div class="flex flex-col md:flex-row gap-4">
            <input type="text" id="name"
                placeholder="Nama gesture (halo)"
                class="flex-1 px-4 py-2 rounded-xl bg-white/10 border border-white/20 focus:outline-none">

            <input type="text" id="pattern"
                placeholder="Pattern contoh: 1,0,0,1"
                class="flex-1 px-4 py-2 rounded-xl bg-white/10 border border-white/20">

            <input type="text" id="text"
                placeholder="Text output"
                class="flex-1 px-4 py-2 rounded-xl bg-white/10 border border-white/20 focus:outline-none">

            <button onclick="addGesture()"
                class="bg-blue-600 hover:bg-blue-700 px-6 py-2 rounded-xl transition">
                + Tambah
            </button>
        </div>
    </div>

    <!-- List Gesture -->
    <div id="gestureList" class="grid md:grid-cols-2 gap-6"></div>

</div>

<script>
async function loadGestures(){
    const res = await fetch('/gestures');
    const data = await res.json();

    let html = "";

    data.forEach(g => {
        html += `
        <div class="bg-white/5 backdrop-blur-xl p-6 rounded-2xl shadow-xl border border-white/10">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">${g.name}</h3>
                <h3 class="text-xl font-bold">${g.pattern}</h3>

                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer"
                        ${g.active ? "checked" : ""}
                        onchange="updateGesture('${g.name}')">
                    <div class="w-11 h-6 bg-gray-600 rounded-full peer peer-checked:bg-green-500 relative transition">
                        <div class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition peer-checked:translate-x-5"></div>
                    </div>
                </label>
            </div>

            <input type="text"
                value="${g.text}"
                id="text-${g.name}"
                class="w-full px-4 py-2 rounded-xl bg-white/10 border border-white/20 mb-4">

            <div class="flex justify-between">
                <button onclick="updateGesture('${g.name}')"
                    class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-xl transition">
                    Update
                </button>

                <button onclick="deleteGesture('${g.name}')"
                    class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-xl transition">
                    Delete
                </button>
            </div>
        </div>
        `;
    });

    document.getElementById("gestureList").innerHTML = html;
}

async function addGesture(){
    await fetch('/add-gesture',{
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body:JSON.stringify({
            name:document.getElementById('name').value,
            text:document.getElementById('text').value,
            pattern:document.getElementById('pattern').value.split(',').map(Number)
        })
    });

    loadGestures();
}

async function updateGesture(name){
    const text = document.getElementById("text-"+name).value;
    const active = document.querySelector(`#text-${name}`)
        .parentNode.querySelector("input[type=checkbox]").checked;

    await fetch('/update-gesture',{
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body:JSON.stringify({name,text,active})
    });

    loadGestures();
}

async function deleteGesture(name){
    if(confirm("Yakin mau hapus gesture ini?")){
        await fetch('/delete-gesture',{
            method:'POST',
            headers:{
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':'{{ csrf_token() }}'
            },
            body:JSON.stringify({name})
        });

        loadGestures();
    }
}

async function startAI(){
    const res = await fetch("/start-ai");
    const data = await res.json();
    alert(data.status);
}

async function stopAI(){
    const res = await fetch("/stop-ai");
    const data = await res.json();
    alert(data.status);
}

function goBack(){
    window.history.back();
}

loadGestures();
</script>

</body>
</html>
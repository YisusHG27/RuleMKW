// ============================================
// APLICACIÓN VUE.JS - RULETA INTERACTIVA
// ============================================

const app = Vue.createApp({
    data() {
        return {
            // Usuario
            usuario: null,
            rol: null,
            userId: null,
            
            // Datos de circuitos y copas
            circuitos: [],
            copas: [],
            cargando: true,
            
            // Estado de la ruleta
            girando: false,
            resultados: [],      // Los 4 circuitos seleccionados
            ganador: null,       // Circuito ganador
            
            // Control de copas abiertas
            copaAbierta: null,   // ID de la copa desplegada
            
            // Mensajes
            mensaje: '',
            tipoMensaje: '',
            
            // Estadísticas
            estadisticas: null,
            
            // Control de animaciones
            posicionIluminada: 0, // 0-3 para las 4 posiciones
            intervaloAnimacion: null
        }
    },
    
    computed: {
        // Circuitos seleccionados
        circuitosSeleccionados() {
            return this.circuitos.filter(c => c.seleccionado);
        },
        
        // Total de seleccionados
        seleccionados() {
            return this.circuitosSeleccionados.length;
        },
        
        // Total de circuitos
        totalCircuitos() {
            return this.circuitos.length;
        },
        
        // Puede girar la ruleta
        puedeGirar() {
            return this.seleccionados >= 4 && 
                   this.usuario && 
                   !this.girando;
        },
        
        // Agrupar circuitos por copa para mostrar
        copasConCircuitos() {
            const grupos = {};
            
            // Primero, crear estructura básica de copas
            this.copas.forEach(copa => {
                grupos[copa.id] = {
                    id: copa.id,
                    nombre: copa.nombre,
                    imagen: copa.imagen || this.obtenerRutaImagenCopa(copa.nombre),
                    circuitos: []
                };
            });
            
            // Luego, agregar circuitos a sus copas
            this.circuitos.forEach(circuito => {
                if (grupos[circuito.copa.id]) {
                    grupos[circuito.copa.id].circuitos.push({
                        ...circuito,
                        // Asegurar que la imagen existe
                        imagen: circuito.imagen || this.obtenerRutaImagenCircuito(circuito)
                    });
                }
            });
            
            return Object.values(grupos).sort((a, b) => a.id - b.id);
        }
    },
    
    async mounted() {
        console.log('🚀 Iniciando aplicación RuleMKW');
        
        // Verificar sesión
        await this.verificarSesion();
        
        // Cargar datos
        await this.cargarDatos();
        
        // Cargar estadísticas si hay usuario
        if (this.usuario) {
            await this.cargarEstadisticas();
        }
        
        console.log('✅ Aplicación lista');
    },
    
    methods: {
        // ============ MANEJO DE DATOS ============
        
        // Verificar sesión del usuario
        async verificarSesion() {
            try {
                const respuesta = await fetch('../backend/api/check_session.php');
                const datos = await respuesta.json();
                
                if (datos.success) {
                    this.usuario = datos.user.nombre;
                    this.rol = datos.user.rol;
                    this.userId = datos.user.id;
                    console.log('👤 Usuario:', this.usuario, 'Rol:', this.rol);
                }
            } catch (error) {
                console.log('No hay sesión activa');
            }
        },
        
        // Cargar todos los datos necesarios
        async cargarDatos() {
            try {
                // Cargar circuitos
                const respuestaCircuitos = await fetch('../backend/api/get_circuits.php');
                const datosCircuitos = await respuestaCircuitos.json();
                
                if (datosCircuitos.success) {
                    // Procesar circuitos
                    this.circuitos = datosCircuitos.circuitos.map(circuito => ({
                        ...circuito,
                        seleccionado: true, // Por defecto seleccionados
                        // Asegurar ruta de imagen
                        imagen: circuito.imagen || this.obtenerRutaImagenCircuito(circuito)
                    }));
                    
                    console.log(`📊 ${this.circuitos.length} circuitos cargados`);
                    
                    // Extraer copas únicas de los circuitos
                    const copasMap = new Map();
                    this.circuitos.forEach(circuito => {
                        if (circuito.copa && !copasMap.has(circuito.copa.id)) {
                            copasMap.set(circuito.copa.id, {
                                id: circuito.copa.id,
                                nombre: circuito.copa.nombre,
                                imagen: circuito.copa.imagen
                            });
                        }
                    });
                    
                    this.copas = Array.from(copasMap.values());
                    console.log(`🏆 ${this.copas.length} copas encontradas`);
                }
            } catch (error) {
                console.error('❌ Error cargando datos:', error);
                this.mostrarMensaje('Error al cargar los circuitos', 'error');
            } finally {
                this.cargando = false;
            }
        },
        
        // Cargar estadísticas del usuario
        async cargarEstadisticas() {
            try {
                const respuesta = await fetch('../backend/api/get_stats.php');
                const datos = await respuesta.json();
                
                if (datos.success) {
                    this.estadisticas = datos.stats;
                    console.log('📈 Estadísticas cargadas');
                }
            } catch (error) {
                console.log('No se pudieron cargar estadísticas');
            }
        },
        
        // ============ MANEJO DE IMÁGENES ============
        
        // Manejar errores al cargar imágenes
        manejarErrorImagen(evento) {
            const img = evento.target;
            console.warn(`⚠️ Error cargando imagen: ${img.src}`);
            
            // Determinar qué tipo de imagen es
            const esCopa = img.classList.contains('imagen-copa');
            const esCircuito = img.classList.contains('imagen-circuito') || 
                               img.src.includes('circuito');
            
            if (esCopa) {
                // Para copas, usar placeholder genérico
                img.src = this.generarPlaceholderCopa(img.alt);
            } else if (esCircuito) {
                // Para circuitos, intentar ruta alternativa
                const nombreArchivo = img.src.split('/').pop();
                const nombreCopa = this.obtenerNombreCopaDeRuta(img.src);
                
                if (nombreCopa) {
                    // Intentar con extensión .jpg si era .png o viceversa
                    const nuevaRuta = `../media/copas/${nombreCopa}/${nombreArchivo
                        .replace('.png', '.jpg')
                        .replace('.jpg', '.png')}`;
                    
                    img.src = nuevaRuta;
                    img.onerror = () => {
                        img.src = this.generarPlaceholderCircuito(img.alt);
                    };
                } else {
                    img.src = this.generarPlaceholderCircuito(img.alt);
                }
            } else {
                // Placeholder genérico
                img.src = this.generarPlaceholderCircuito(img.alt);
            }
            
            img.alt = 'Imagen no disponible';
        },
        
        // Obtener nombre de copa desde ruta de imagen
        obtenerNombreCopaDeRuta(ruta) {
            const match = ruta.match(/copas\/([^\/]+)\//);
            return match ? match[1] : null;
        },
        
        // Generar ruta de imagen para una copa
        obtenerRutaImagenCopa(nombreCopa) {
            // Convertir nombre de copa a formato de carpeta
            const nombreCarpeta = this.convertirNombreACarpeta(nombreCopa);
            return `../media/copas/${nombreCarpeta}/${nombreCarpeta}.png`;
        },
        
        // Generar ruta de imagen para un circuito
        obtenerRutaImagenCircuito(circuito) {
            const nombreCopaCarpeta = this.convertirNombreACarpeta(circuito.copa.nombre);
            const nombreCircuitoArchivo = this.convertirNombreAArchivo(circuito.nombre);
            
            return `../media/copas/${nombreCopaCarpeta}/${nombreCircuitoArchivo}.jpg`;
        },
        
        // Convertir nombre a formato de carpeta (ej: "Copa Champiñón" -> "champ")
        convertirNombreACarpeta(nombre) {
            const mapeo = {
                'Copa Champiñón': 'champ',
                'Copa Flor': 'flor',
                'Copa Estrella': 'estrella',
                'Copa Caparazón': 'caparazon',
                'Copa Plátano': 'platano',
                'Copa Hoja': 'hoja',
                'Copa Centella': 'rayo',
                'Copa Especial': 'especial'
            };
            
            return mapeo[nombre] || nombre.toLowerCase().replace(/\s+/g, '_');
        },
        
        // Convertir nombre de circuito a archivo (ej: "Circuito Mario Bros." -> "CircuitoMarioBros")
        convertirNombreAArchivo(nombre) {
            return nombre
                .replace(/\s+/g, '')
                .replace(/[()]/g, '')
                .replace(/[^\w]/g, '');
        },
        
        // Generar placeholder SVG para copas
        generarPlaceholderCopa(texto) {
            const inicial = texto.charAt(0).toUpperCase();
            const color = '#FF9900'; // Naranja para copas
            
            return `data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="200" height="120" viewBox="0 0 200 120">
                <rect width="200" height="120" fill="%23222" rx="10"/>
                <circle cx="100" cy="50" r="30" fill="${color.replace('#', '%23')}"/>
                <text x="100" y="55" font-family="Arial" font-size="24" fill="white" text-anchor="middle" dy=".3em">${inicial}</text>
                <text x="100" y="95" font-family="Arial" font-size="12" fill="%23aaa" text-anchor="middle">${texto}</text>
            </svg>`;
        },
        
        // Generar placeholder SVG para circuitos
        generarPlaceholderCircuito(texto) {
            const inicial = texto.charAt(0).toUpperCase();
            const color = '#3366FF'; // Azul para circuitos
            
            return `data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
                <rect width="100" height="100" fill="%23333" rx="10"/>
                <circle cx="50" cy="40" r="25" fill="${color.replace('#', '%23')}"/>
                <text x="50" y="45" font-family="Arial" font-size="20" fill="white" text-anchor="middle" dy=".3em">${inicial}</text>
                <text x="50" y="80" font-family="Arial" font-size="10" fill="%23aaa" text-anchor="middle">${texto.substring(0, 15)}</text>
            </svg>`;
        },
        
        // ============ SELECCIÓN DE CIRCUITOS ============
        
        // Alternar selección de un circuito
        alternarCircuito(circuito) {
            circuito.seleccionado = !circuito.seleccionado;
            console.log(`🔘 ${circuito.nombre}: ${circuito.seleccionado ? '✓' : '✗'}`);
        },
        
        // Seleccionar todos los circuitos
        seleccionarTodos() {
            this.circuitos.forEach(circuito => {
                circuito.seleccionado = true;
            });
            this.mostrarMensaje('✅ Todos los circuitos seleccionados', 'success');
        },
        
        // Deseleccionar todos los circuitos
        deseleccionarTodos() {
            this.circuitos.forEach(circuito => {
                circuito.seleccionado = false;
            });
            this.mostrarMensaje('ℹ️ Todos los circuitos deseleccionados', 'info');
        },
        
        // Seleccionar todos los circuitos de una copa específica
        seleccionarTodosEnCopa(copaId) {
            this.circuitos.forEach(circuito => {
                if (circuito.copa.id === copaId) {
                    circuito.seleccionado = true;
                }
            });
            console.log(`✅ Todos los circuitos de la copa ${copaId} seleccionados`);
        },
        
        // Deseleccionar todos los circuitos de una copa
        deseleccionarTodosEnCopa(copaId) {
            this.circuitos.forEach(circuito => {
                if (circuito.copa.id === copaId) {
                    circuito.seleccionado = false;
                }
            });
            console.log(`❌ Todos los circuitos de la copa ${copaId} deseleccionados`);
        },
        
        // Contar circuitos seleccionados en una copa
        contarSeleccionadosEnCopa(copaId) {
            return this.circuitos.filter(circuito => 
                circuito.copa.id === copaId && circuito.seleccionado
            ).length;
        },
        
        // Abrir/cerrar el panel de circuitos de una copa
        alternarCopa(copaId) {
            if (this.copaAbierta === copaId) {
                this.copaAbierta = null; // Cerrar
            } else {
                this.copaAbierta = copaId; // Abrir
            }
        },
        
        // ============ RULETA Y ANIMACIONES ============
        
        // Girar la ruleta
        async girarRuleta() {
            // Validaciones
            if (this.seleccionados < 4) {
                this.mostrarMensaje('❌ Selecciona al menos 4 circuitos', 'error');
                return;
            }
            
            if (!this.usuario) {
                this.mostrarMensaje('❌ Debes iniciar sesión para usar la ruleta', 'error');
                return;
            }
            
            // Iniciar proceso
            this.iniciarRuleta();
            
            try {
                // PASO 1: Seleccionar 4 circuitos aleatorios
                await this.seleccionarCircuitosAleatorios();
                
                // PASO 2: Animación de iluminación secuencial
                await this.animarIluminacionSecuencial();
                
                // PASO 3: Seleccionar ganador
                await this.seleccionarGanador();
                
                // PASO 4: Guardar en base de datos
                await this.guardarTirada();
                
                // PASO 5: Actualizar estadísticas
                await this.actualizarEstadisticas();
                
                this.mostrarMensaje(`🏆 ¡GANADOR: ${this.ganador.nombre}!`, 'success');
                
            } catch (error) {
                console.error('❌ Error en la ruleta:', error);
                this.mostrarMensaje('❌ Error al girar la ruleta', 'error');
            } finally {
                this.finalizarRuleta();
            }
        },
        
        // Inicializar estado de la ruleta
        iniciarRuleta() {
            this.girando = true;
            this.resultados = [];
            this.ganador = null;
            this.posicionIluminada = 0;
            this.mensaje = '';
            
            // Limpiar intervalos previos
            if (this.intervaloAnimacion) {
                clearInterval(this.intervaloAnimacion);
            }
        },
        
        // Seleccionar 4 circuitos aleatorios
        async seleccionarCircuitosAleatorios() {
            const disponibles = this.circuitosSeleccionados;
            const seleccionados = [];
            const indicesUsados = new Set();
            
            // Seleccionar 4 circuitos únicos
            while (seleccionados.length < 4 && seleccionados.length < disponibles.length) {
                const indiceAleatorio = Math.floor(Math.random() * disponibles.length);
                
                if (!indicesUsados.has(indiceAleatorio)) {
                    const circuito = disponibles[indiceAleatorio];
                    
                    // Asegurar que el circuito tiene imagen
                    const circuitoConImagen = {
                        ...circuito,
                        imagen: circuito.imagen || this.obtenerRutaImagenCircuito(circuito),
                        ganador: false
                    };
                    
                    seleccionados.push(circuitoConImagen);
                    indicesUsados.add(indiceAleatorio);
                    
                    // Mostrar con pequeña pausa para efecto visual
                    await this.esperar(300);
                    this.resultados = [...seleccionados];
                }
            }
            
            console.log('🎯 4 circuitos seleccionados para la ruleta');
            this.mostrarMensaje('Circuitos seleccionados. Girando...', 'success');
        },
        
        // Animación de iluminación secuencial entre las 4 posiciones
        async animarIluminacionSecuencial() {
            return new Promise(resolve => {
                let ciclos = 0;
                const ciclosTotales = 8; // Número de ciclos de iluminación
                
                this.intervaloAnimacion = setInterval(() => {
                    // Avanzar a la siguiente posición (0-3)
                    this.posicionIluminada = (this.posicionIluminada + 1) % 4;
                    
                    // Incrementar contador de ciclos
                    ciclos++;
                    
                    // Detener después de los ciclos completos
                    if (ciclos >= ciclosTotales * 4) {
                        clearInterval(this.intervaloAnimacion);
                        this.posicionIluminada = -1; // Apagar todas
                        resolve();
                    }
                }, 150); // Velocidad de la animación
            });
        },
        
        // Seleccionar un ganador de los 4 circuitos
        async seleccionarGanador() {
            // Esperar un momento para dramatismo
            await this.esperar(1000);
            
            // Seleccionar ganador aleatorio
            const indiceGanador = Math.floor(Math.random() * this.resultados.length);
            
            // Marcar ganador
            this.resultados.forEach((circuito, indice) => {
                circuito.ganador = (indice === indiceGanador);
            });
            
            this.ganador = this.resultados[indiceGanador];
            
            // Efecto visual especial para el ganador
            this.animarGanador();
            
            console.log(`🏆 Ganador: ${this.ganador.nombre}`);
        },
        
        // Animación especial para el ganador
        animarGanador() {
            // Pequeño efecto de confeti
            this.crearConfeti();
            
            // Scroll suave al ganador
            setTimeout(() => {
                const elementoGanador = document.querySelector('.slot-ruleta.ganador');
                if (elementoGanador) {
                    elementoGanador.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            }, 500);
        },
        
        // Crear efecto de confeti
        crearConfeti() {
            const colores = ['#3366FF', '#FF3366', '#33FF66', '#FFCC00'];
            const app = document.getElementById('app');
            
            for (let i = 0; i < 20; i++) {
                setTimeout(() => {
                    const confeti = document.createElement('div');
                    confeti.className = 'confeti';
                    confeti.style.cssText = `
                        position: fixed;
                        width: 10px;
                        height: 10px;
                        background: ${colores[Math.floor(Math.random() * colores.length)]};
                        border-radius: 50%;
                        pointer-events: none;
                        z-index: 9999;
                        top: ${Math.random() * 100}vh;
                        left: ${Math.random() * 100}vw;
                        animation: caerConfeti 1s ease-out forwards;
                    `;
                    
                    app.appendChild(confeti);
                    
                    // Remover después de la animación
                    setTimeout(() => confeti.remove(), 1000);
                }, i * 50);
            }
        },
        
        // Guardar la tirada en la base de datos
        async guardarTirada() {
            if (!this.ganador || !this.userId) return;
            
            try {
                await fetch('../backend/api/save_spin.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        circuito_id: this.ganador.id
                    })
                });
                
                console.log('Tirada guardada en la base de datos');
            } catch (error) {
                console.warn('No se pudo guardar la tirada:', error);
            }
        },
        
        // Actualizar estadísticas después de la tirada
        async actualizarEstadisticas() {
            if (this.usuario) {
                await this.cargarEstadisticas();
            }
        },
        
        // Finalizar el proceso de la ruleta
        finalizarRuleta() {
            // Limpiar intervalo de animación
            if (this.intervaloAnimacion) {
                clearInterval(this.intervaloAnimacion);
                this.intervaloAnimacion = null;
            }
            
            // Apagar iluminación
            this.posicionIluminada = -1;
            
            // Permitir nuevo giro después de un momento
            setTimeout(() => {
                this.girando = false;
            }, 2000);
        },
        
        // ============ UTILIDADES ============
        
        // Esperar un tiempo determinado
        esperar(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        },
        
        // Mostrar mensajes temporales
        mostrarMensaje(texto, tipo) {
            this.mensaje = texto;
            this.tipoMensaje = tipo;
            
            setTimeout(() => {
                if (this.mensaje === texto) {
                    this.mensaje = '';
                    this.tipoMensaje = '';
                }
            }, 4000);
        }
    }
});

// Añadir estilos CSS dinámicamente para animaciones
document.addEventListener('DOMContentLoaded', function() {
    const estilos = document.createElement('style');
    estilos.textContent = `
        /* Animación de confeti */
        @keyframes caerConfeti {
            0% { 
                transform: translateY(-100px) rotate(0deg); 
                opacity: 1; 
            }
            100% { 
                transform: translateY(100vh) rotate(360deg); 
                opacity: 0; 
            }
        }
        
        /* Clase para slots iluminados */
        .slot-iluminado {
            position: relative;
            overflow: hidden;
        }
        
        .slot-iluminado::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent 30%,
                rgba(255, 255, 255, 0.1) 50%,
                transparent 70%
            );
            animation: brilloMovil 2s linear infinite;
        }
        
        @keyframes brilloMovil {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Transiciones suaves */
        .slot-ruleta {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .circuito-desplegable {
            transition: all 0.2s ease;
        }
        
        /* Mejoras visuales para imágenes */
        .imagen-circuito img, .imagen-copa img {
            transition: transform 0.3s ease;
        }
        
        .imagen-circuito img:hover, .imagen-copa img:hover {
            transform: scale(1.05);
        }
    `;
    document.head.appendChild(estilos);
});

// Montar la aplicación
app.mount('#app');

console.log('🎮 Aplicación RuleMKW cargada y lista');
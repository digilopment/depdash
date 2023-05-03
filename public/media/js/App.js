const depDashDataSource = () => document.currentScript.getAttribute('data-source') || '/json/data.json';
const renderBeforeSource = () => document.currentScript.getAttribute('data-render-before') || '';

const depDashMainApp = async () => {

    const showTemplate = true;
    const enableRenderBeforeSource = true;
    let renderBefore = 'WC TV Noviny SK CMS';

    if (renderBeforeSource() && enableRenderBeforeSource) {
        renderBefore = renderBeforeSource();
    }

    var scriptUrls = [
    ];
    for (var i = 0; i < scriptUrls.length; i++) {
        var script = document.createElement('script');
        script.src = scriptUrls[i];
        document.body.appendChild(script);
    }

    const response = await fetch(depDashDataSource());
    const data = await response.json();
    const environments = findUniqueEnvironmentsName(data);
    const template = `
    ${environments.map((envId) => {
        const dockerStatus = data[envId].dockerStatuses.docker_ps[0] ? "RUNNING" : "STOPPED";
        const dockerColor = data[envId].dockerStatuses.docker_ps[0] ? "green" : "red";
        const containers = data[envId].dockerStatuses.docker_ps.length;
        const repositories = data.length;
        const envName = data[envId].environmentStatuses[0].environment.name;
        if (showTemplate) {
            return `
            <div id="${envName}">
               <!--<a href="#${envName}" style="font-size:10px" target="_blank"><img style="padding-bottom:20px" src="/media/img/docker-small.jpg" alt="" /></a>-->
              <h2 id="" style="display:inline"><a class="repository-name" href="#${name.replace(/\s+/g, '')}" style="color: black;text-decoration:none;" target="_blank">Docker ENV:</a>
                <b>${envName}</b> 
                is <small><span style="color:${dockerColor}"><b>${dockerStatus}</b></span></small>
                on <b><span style="color:${dockerColor}">${containers}</span> containers</b>
                <!--with <b><span style="color:${dockerColor}">${containers}</span> images</b>-->
              </h2>
              <table class="table">
                <thead>
                  <tr>
                    <th style="width: 16%">#</th>
                    <th style="width: 16%">Container</th>
                    <th style="width: 16%">Image</th>
                    <th style="width: 20%;text-align:center">Status</th>
                    <th style="width: 16%">Up</th>
                    <th style="width: 16%">Ports</th>
                  </tr>
                </thead>
                <tbody>
                  ${data[envId].dockerStatuses.docker_ps.map(({ container_id, names, image, status, ports = '' }) => `
                    <tr style="font-size: 13px;">
                      <td>${container_id}</td>
                      <td><b>${names}</b></td>
                      <td>${image}</td>
                      <td style="color: ${status ? 'green' : 'red'};text-align:center">
                        ${status ? 'RUNNING' : 'STOPPED'}
                      </td>
                      <td>${status}</td>
                      <td>${ports.replace(/, ?/g, '<br>').replace(/\/tcp/g, '')}</td>
                    </tr>
                  `).join('')}
                </tbody>
              </table>
            </div>
            
            ${data.map(({ deploymentProject: { name }, environmentStatuses, dockerStatuses }, i) => {
                if (environmentStatuses[0].environment.name === envName) {
                    return `
                <div id="${name.replace(/\s+/g, '')}">
                  <!--<a href="#${name.replace(/\s+/g, '')}" style="font-size:10px" target="_blank">
                    <img style="padding-bottom:13px" src="/media/img/markiza-small.jpg" alt="" />
                  </a>-->
                  <a class="repository-name" href="#${name.replace(/\s+/g, '')}" style="color: black;text-decoration:none;" target="_blank">
                    <h2 style="display:inline">${name}</h2>
                  </a>
                  <table class="table">
                    <thead>
                      <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 15%">Name</th>
                        <th style="width: 34%">Branch</th>
                        <th style="width: 12%">Status</th>
                        <th style="width: 13%">Finished</th>
                        <th style="width: 26%">Triggered by</th>
                      </tr>
                    </thead>
                    <tbody>
                      ${environmentStatuses.map(({ environment: { id, name, description, repoUrl }, deploymentResult: { deploymentState, deploymentVersionName, finishedDate, reasonSummary, totalCommits } }) => `
                        <tr>
                          <th scope="row"><a target="_blank" href="${repoUrl}">${id}</a></th>
                          <td>${description} <a href="#${envName}">${name}</a></td>
                          <td>${deploymentVersionName} <br/><small><i><b>Total commits: </b>${totalCommits}</i></small></td>
                          <td style="color: ${{SUCCESS: 'green', UNKNOWN: 'orange', FAILED: 'red'}[deploymentState] || ''}">
                            ${deploymentState === 'UNKNOWN' ? 'IN PROGRESS' : deploymentState}
                          </td>
                          <td>${finishedDate ? timeAgo(finishedDate) : ''}</td>
                          <td>${reasonSummary}</td>
                        </tr>
                      `).join('')}
                    </tbody>
                  </table>
                </div>
                <br>
              `;
                } else {
                    return '';
            }
            }).join('')}
        `;
        }
    }).join('')}`;


    function timeAgo(finishedDate) {
        var seconds = Math.floor((new Date() - new Date(finishedDate)) / 1000);
        var interval = {
            year: 31536000,
            month: 2592000,
            week: 604800,
            day: 86400,
            hour: 3600,
            minute: 60
        };
        for (var key in interval) {
            if (seconds >= interval[key]) {
                var count = Math.floor(seconds / interval[key]);
                return count + ' ' + key + (count > 1 ? 's' : '') + ' ago';
            }
        }
        return 'just now';
    }


    function findUniqueEnvironmentsName(data) {
        const uniqueEnvironments = {};
        const result = [];
        for (let i = 0; i < data.length; i++) {
            const env = data[i].environmentStatuses[0].environment.name.replace(/\s/g, '');
            if (!uniqueEnvironments[env]) {
                uniqueEnvironments[env] = env;
                result.push(i);
            }
        }
        return result;
    }

    function renderTemplateBeforeElement(renderBefore, template) {
        const depDashRoot = 'mkz-sk-enviroments';
        const headings = document.querySelectorAll('h2');
        let elementFound = false;
        for (let i = 0; i < headings.length; i++) {
            const heading = headings[i];
            if (heading.textContent.trim() === renderBefore) {
                elementFound = true;
                const newDiv = document.createElement('div');
                newDiv.setAttribute('id', depDashRoot);
                heading.parentNode.insertBefore(newDiv, heading);
                break;
            }
        }

        if (!elementFound) {
            const h1s = document.querySelectorAll('.col-12');
            if (h1s.length > 0) {
                const newDiv = document.createElement('div');
                newDiv.setAttribute('id', depDashRoot);
                h1s[0].parentNode.insertBefore(newDiv, h1s[0].nextSibling);
            } else {
                console.error('Could not find element to render the template.');
                return;
            }
        }

        var elem = document.getElementById(depDashRoot);
        if (elem) {
            elem.innerHTML = template;
        } else {
            console.error('Failed to insert the new element.');
        }
    }

    function highlightElement() {
        const urlFragment = window.location.hash;
        if (urlFragment) {
            const elementId = urlFragment.slice(1);
            const element = document.getElementById(elementId);
            if (element) {
                element.style.border = '6px solid red';
                element.style.marginLeft = '-6px';
                element.style.marginRight = '-6px';
                element.scrollIntoView({behavior: "smooth"});
            }
        }
    }

    renderTemplateBeforeElement(renderBefore, template);
    highlightElement();

};
depDashMainApp();

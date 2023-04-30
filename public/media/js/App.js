const dataSource = () => document.currentScript.getAttribute('data-source') || '/json/data.json';

const depDashMainApp = async () => {
    const response = await fetch(dataSource());
    const data = await response.json();

    const dockerStatus = data[0].dockerStatuses.docker_ps[0] ? "RUNNING" : "STOPPED";
    const dockerColor = data[0].dockerStatuses.docker_ps[0] ? "green" : "red";
    const containers = data[0].dockerStatuses.docker_ps.length;
    const repositories = data.length;
    const envName = data[0].environmentStatuses[0].environment.name;

    const template = `
    <div id="${envName}">
       <a href="#${envName}" style="font-size:10px" target="_blank"><img style="padding-bottom:20px" src="/media/img/docker-small.jpg" alt="" /></a>
      <h2 id="" style="display:inline">DockerENV: 
        <b>${envName}</b> 
        is <small><span style="color:${dockerColor}"><b>${dockerStatus}</b></span> 
        on <b><span style="color:${dockerColor}">${containers}</span> containers</b>
        with <b><span style="color:${dockerColor}">${repositories}</span> repositories</b>
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
          ${data[0].dockerStatuses.docker_ps.map(({ container_id, names, image, status, ports = '' }) => `
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
    ${data.map(({ deploymentProject: { name }, environmentStatuses, dockerStatuses }) => `
      <div id="${name.replace(/\s+/g, '')}">
        <a href="#${name.replace(/\s+/g, '')}" style="font-size:10px" target="_blank"><img style="padding-bottom:13px" src="/media/img/markiza-small.jpg" alt="" /></a>
        <h2 style="display:inline">${name}</h2>
        <table class="table">
          <thead>
            <tr>
            <th style="width: 5%">#</th>
            <th style="width: 15%">Name</th>
            <th style="width: 15%">Branch</th>
            <th style="width: 12%">Status</th>
            <th style="width: 13%">Finished</th>
            <th style="width: 45%">Triggered by</th>
          </tr>
          </thead>
          <tbody>
            ${environmentStatuses.map(({ environment: { id, name, repoUrl }, deploymentResult: { deploymentState, deploymentVersionName, finishedDate, reasonSummary, totalCommits } }) => `
              <tr>
                <th scope="row"><a target="_blank" href="${repoUrl}">${id}</a></th>
                <td><a href="#${envName}">${name}</a></td>
                <td>${deploymentVersionName} <br/><small><i><b>${totalCommits}</b> commits</i></small></td>
                <td style="color: ${{SUCCESS: 'green', UNKNOWN: 'orange', FAILED: 'red'}[deploymentState] || ''}">
                  ${deploymentState === 'UNKNOWN' ? 'IN PROGRESS' : deploymentState}
                </td>
                <td>${finishedDate ? moment(finishedDate).fromNow() : ''}</td>
                <td>${reasonSummary}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>
      <br>
    `).join('')}`;
    var elem = document.getElementById('mkz-sk-enviroments');
    if (elem) {
        elem.innerHTML = template;
    }
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

};
depDashMainApp();

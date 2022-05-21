using AutoMapper;

using DataCenter.Data.Dtos;
using DataCenter.Data.Models;
using DataCenter.Infrastructure.Providers;
using DataCenter.Infrastructure.Providers.Interfaces;
using DataCenter.Infrastructure.Services.Interfaces;

using System.Collections.Generic;
using System.Threading.Tasks;

namespace DataCenter.Infrastructure.Services
{
    public class TokenService : IService, ITokenService
    {
        private readonly ITokenProvider tokenProvider;

        private readonly IAuthorizationProvider unitProvider;

        private readonly IDatabaseProvider databaseProvider;

        private readonly IMapper mapper;

        public TokenService(
            TokenProvider tokenProvider,
            AuthorizationProvider unitProvider,
            DatabaseProvider databaseProvider,
            Mapper mapper)
        {
            this.tokenProvider = tokenProvider;
            this.unitProvider = unitProvider;
            this.databaseProvider = databaseProvider;
            this.mapper = mapper;
        }

        public async Task<string> GenerateToken(string tag = null)
        {
            string generatedToken = tokenProvider.GenerateAuthToken();

            Credentials credentials = GetCredentialsModel(generatedToken, tag);

            await databaseProvider.CreateAsync(credentials);
            await databaseProvider.CommitAsync();

            unitProvider.AddAccessToken(generatedToken);

            return generatedToken;
        }

        public async Task RemoveToken(string token)
        {
            databaseProvider.Delete<Credentials>(credentials => credentials.GeneratedToken == token);
            await databaseProvider.CommitAsync();

            unitProvider.RemoveAccessToken(token);
        }

        public List<CredentialsDto> GetTokens()
        {
            List<Credentials> credentials = databaseProvider.GetAll<Credentials>();

            return mapper.Map<List<CredentialsDto>>(credentials);
        }

        private Credentials GetCredentialsModel(string generatedToken, string tag)
        {
            return new Credentials
            {
                GeneratedToken = generatedToken,
                Tag = tag
            };
        }
    }
}